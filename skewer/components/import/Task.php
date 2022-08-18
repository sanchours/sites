<?php

namespace skewer\components\import;

use skewer\base\log\models\Log;
use skewer\base\orm\Query;
use skewer\base\queue;
use skewer\base\site\Site;
use skewer\build\Catalog\Goods;
use skewer\components\catalog;
use skewer\components\catalog\Api as CatalogApi;
use skewer\components\garbage\Garbage;
use skewer\components\import\Api as ImportApi;
use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\field\Active;
use skewer\components\import\field\DictCollection;
use skewer\components\import\provider\Prototype;
use skewer\components\seo;
use skewer\helpers\Mailer;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Задача импорта
 * Class Task.
 */
class Task extends queue\Task
{
    public static function className()
    {
        return get_called_class();
    }

    /** Импорт начался */
    const importStart = 1;

    /** Импорт в процессе */
    const importProcess = 2;

    /** Импорт заканчивается */
    const importFinish = 3;

    /** @var Prototype */
    private $provider;

    /** @var catalog\GoodsRow текущая запись для обработки */
    public $goodsRow = false;

    /** @var field\Prototype[] набор обработчиков полей */
    private $fields = [];

    /** @var Logger Логгер */
    private $logger;

    /** @var Config Конфиг */
    private $config;

    /** @var bool флаг пропуска текущей строки */
    protected $skipCurrentRow = false;

    /** @var string имя каталожной карточки */
    protected $cardName = '';

    public static $sHashFieldName = 'import_hash';

    public static $sUpdatedFieldName = 'updated';

    /** @var bool флаг удаления обрабатываемого товара */
    protected $deleteCurrentGood = false;

    private $timeDelImportLog = '-30 days';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        try {
            $aArgs = func_get_args();

            $iTpl = (isset($aArgs[0]['tpl'])) ? $aArgs[0]['tpl'] : 0;

            if (!$iTpl) {
                throw new \Exception('template not defined');
            }

            $iStartTime = time();

            /* Логгер */
            $this->logger = new Logger($this->getId(), $iTpl);

            $this->logger->setParam('start', date('Y-m-d H:i:s', $iStartTime));

            $oTemplate = Api::getTemplate($iTpl);

            if (!$oTemplate) {
                throw new \Exception('template not found');
            }

            /* Собираем конфиг */
            $this->config = new Config($oTemplate);

            /* Установка карточки */
            $this->cardName = $this->config->getParam('card');
            if (!$this->cardName) {
                $this->fail('card name is not set');
            }

            /* Выкачиваем файл если надо */
            if ($this->config->getParam('type') == Api::Type_Url) {
                $this->config->setParam('file', Api::uploadFile($this->config->getParam('source')));
            }

            /* Получаем провайдер данных */
            $this->provider = Api::getProvider($this->config);

            // удаляем старые логи
            $aOldTasks = (new \yii\db\Query())
                ->select(['task'])
                ->from(\skewer\components\import\ar\Log::getTableName())
                ->where(['tpl' => $iTpl])
                ->andWhere(['name' => 'start'])
                ->andWhere(['<', 'value', date('Y-m-d H:m:s', strtotime('-30 days'))])
                ->all();

            (new \yii\db\Query())->createCommand()->delete(\skewer\components\import\ar\Log::getTableName(), ['task' => ArrayHelper::getColumn($aOldTasks, 'task')])->execute();

            /** Копируем файл импорта в директорию с мусором */
            $sCopyFilePath = Garbage::copyToGarbageDir($this->provider->getFile(), $iStartTime);
            $this->logger->setParam('copy_file', $sCopyFilePath);

            // удаляем старые логи если включен флажок
            if ($this->config->getParam('clear_log')) {
                ImportApi::deleteOldLogsByTplId($iTpl, $this->timeDelImportLog);
            }

            /* Импорт начинается */
            $this->config->setParam('importStatus', self::importStart);

            $this->logger->setParam('new', 0);
            $this->logger->setParam('update', 0);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function recovery()
    {
        try {
            $aArgs = func_get_args();

            if (!isset($aArgs[0]['data'])) {
                throw new \Exception('no valid data');
            }
            /* Собираем конфиг */
            $this->config = new Config();
            $this->config->setData(json_decode($aArgs[0]['data'], true));

            /* Логгер */
            $this->logger = new Logger($this->getId(), $this->config->getParam('id'));

            /* Установка карточки */
            $this->cardName = $this->config->getParam('card');
            if (!$this->cardName) {
                $this->fail('card name is not set');
            }

            /* Получаем профайдер данных */
            $this->provider = Api::getProvider($this->config);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        $this->logger->setSaved(['new_list', 'update_list', 'skip_list']);

        /* Инициализация обработчиков полей */
        $this->loadFields();

        $this->provider->beforeExecute();

        $this->config->setParam('importStatus', self::importProcess);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        Log::disableLogs();
        /* Если провайдер не разрешает читать - прерываемся */
        if (!$this->provider->canRead()) {
            $this->setStatus(static::stInterapt);

            return false;
        }

        // удаляем запись из памяти
        if ($this->goodsRow) {
            $this->goodsRow = false;
        }
        $this->config->setParam('current_title', '');

        $this->skipCurrentRow(false);

        $this->deleteCurrentGood(false);

        /** Получение данных */
        $aBuffer = $this->provider->getRow();

        /* Данных нет - завершаем импорт */
        if ($aBuffer === false) {
            $this->setStatus(static::stComplete);
            $this->config->setParam('importStatus', self::importFinish);

            return true;
        }

        $sBufferHash = md5(implode('_', $aBuffer));

        /*Если в настройках включена проверка по хэшу И хэш ноды НЕ изменился*/
        if ($this->config->getParam('use_goods_hash') && $this->checkHash($sBufferHash)) {
            $this->skipCurrentRow = true;
        }

//            /*Если в настройках стоит "обновлять измененные вручную" И хэш ноды изменился*/
//            if (SysVar::get('import.rewrite_good') && !$this->checkHash($sBufferHash))
//                $this->skipCurrentRow = false;

        //Передаем данные в поля
        $this->loadDataFields($aBuffer);

        // проводим операции перед обработкой данных
        $this->beforeExecuteFields();

        // если есть флаг удаления товара
        if ($this->deleteCurrentGood && $this->goodsRow) {
            $this->deleteGood();

        // если есть флаг пропуска или отсутствует строка
        } elseif ($this->skipCurrentRow or !$this->goodsRow) {
            //пропуск строки
            $this->skip();
        } else {
            //Собираем данные с полей
            $this->executeFields();

            //Операции перед сохранением
            $this->beforeSaveFields();

            /*Если нужно Хэшировать ноду - захешируем ее*/
            if ($this->config->getParam('use_goods_hash')) {
                $this->goodsRow->setField(self::$sHashFieldName, $sBufferHash);
            }

            if (!$this->skipCurrentRow) {
                //Сохранение товара
                $this->saveGoodsRow();

                // производим действия после сохранения
                $this->afterSaveFields();

                // обновление поискового индекса
                $oSearch = new Goods\Search();
                $oSearch->setEntity($this->goodsRow);
                $oSearch->updateByObjectId($this->goodsRow->getRowId(), false);
            }
        }

        // чистим переменные
        foreach ($this->getFields() as $oField) {
            $oField->dropDown();
        }

        Log::enableLogs();

        return true;
    }

    /**
     * Проверяет наличие хэша записи в таблице товаров.
     *
     * @param $sHash
     *
     * @return bool
     */
    private function checkHash($sHash)
    {
        $iCount = Query::SelectFrom('co_base_card')
            ->where(self::$sHashFieldName, $sHash)
            ->getCount('id');

        return (bool) $iCount;
    }

    /**
     * {@inheritdoc}
     */
    public function afterExecute()
    {
        $this->provider->afterExecute();

        foreach ($this->getFields() as $oField) {
            $oField->shutdown();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reservation()
    {
        $this->setParams(['data' => $this->config->getJsonData()]);

        $this->logger->setParam('status', static::stFrozen);
        $this->logger->save();
    }

    /**
     * {@inheritdoc}
     */
    public function error()
    {
        /*
         * Ошибка!
         * Корректно посохранять все логи и закрыть все соединения
         */
        if ($this->logger) {
            $this->logger->setParam('status', static::stError);
            $this->logger->setParam('finish', date('Y-m-d H:i:s'));
            $this->logger->save();
        }

        $aConfig = [
            'title' => $this->getConfig()->getParam('title'),
            'send_error' => $this->getConfig()->getParam('send_error'),
        ];
        Api::sendMailAdminAboutErrors($aConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function complete()
    {
        $iHideMode = Active::hideNone;

        foreach ($this->getFields() as $oField) {
            if ($oField->getName() == 'active') {
                if (mb_strripos($oField::className(), 'Active') !== false) {
                    $iHideMode = $oField->getHide();
                } else {
                    throw new UserException(\Yii::t('import', 'no_active_field'));
                }
            }
        }

        //В зависимости от режима установки активности, обойдем товары и проставим активность.
        switch ($iHideMode) {
            case Active::hideAll:
                /* Скрываем все */
                catalog\GoodsSelector::deactivateNonUpdated();
                catalog\GoodsSelector::resetSearchAfterDeactivation();
                catalog\GoodsSelector::resetUpdated();
                break;

            case Active::hideFromCard:
                /* Скрываем все внутри карточки */
                catalog\GoodsSelector::deactivateNonUpdated($this->cardName);
                catalog\GoodsSelector::resetSearchAfterDeactivation();
                catalog\GoodsSelector::resetUpdated($this->cardName);
                break;
        }

        if ($this->getConfig()->getParam(DictCollection::NEW_COLLECTIONS) !== '') {
            $this->sendMailAdminAboutCollections($this->getConfig()->getParam(DictCollection::NEW_COLLECTIONS));
        }

        /*
         * конец. Сохраним логи.
         */
        if ($this->logger) {
            $this->logger->setParam('status', static::stComplete);
            $this->logger->setParam('finish', date('Y-m-d H:i:s'));
            $this->logger->save();
        }

        try {
            // добавляем задачу на обновление sitemap.xml
            seo\Service::updateSiteMap();
        } catch (\Exception $e) {
            $this->logger->setListParam('error_list', 'Error makeSiteMap: ' . $e->getMessage());
        }

        if (Api::needSendNotify($this->getConfig())) {
            Api::sendNotifyMail($this->getConfig(), $this->getLogger());
        }
    }

    /**
     * Ошибка!
     *
     * @param \Exception|string $msg
     */
    private function fail($msg)
    {
        $msg = ($msg instanceof \Exception) ? $msg->getMessage() : $msg;
        if ($this->logger) {
            $this->logger->setListParam('error_list', $msg);
        }

        if ($msg instanceof \Exception) {
            \skewer\base\log\Logger::dumpException($msg);
        } else {
            \skewer\base\log\Logger::dump($msg);
        }

        $this->setStatus(static::stError);
    }

    /**
     * Инициализация обработчиков полей по соответствию полей и типов.
     */
    private function loadFields()
    {
        $aConfigFields = $this->config->getParam('fields');

        if (!$aConfigFields) {
            $this->fail(\Yii::t('import', 'error_fields_not_found'));

            return false;
        }

        $bUnique = false;
        /* Перебираем поля из конфига */
        foreach ($aConfigFields as $aField) {
            if (!$aField['type']) {
                continue;
            }

            $sClassName = 'skewer\\components\\import\\field\\' . $aField['type'];

            if (class_exists($sClassName)) {
                /** Создаем обработчики полей */
                $oField = new $sClassName(explode(',', $aField['importFields']), $aField['name'], $this);

                if (!$oField instanceof field\Prototype) {
                    $this->fail('No valid field [' . $aField['type'] . ']');

                    return false;
                }

                if ($oField->skipField()) {
                    continue;
                }

                if ($oField->isUnique()) {
                    $bUnique = $oField->isUnique();
                }

                /* Начальная инициализация */
                try {
                    $oField->init();
                } catch (\Exception $e) {
                    $this->fail($e);

                    return false;
                }

                $this->fields[] = $oField;
            } else {
                $this->fail('cant find field format [' . $aField['type'] . ']');

                return false;
            }
        }

        if (!$bUnique) {
            $this->fail(\Yii::t('import', 'error_unique_field_not_found'));

            return false;
        }

        return true;
    }

    /**
     * Передаем данные в поля.
     *
     * @param $aBuffer
     */
    private function loadDataFields($aBuffer)
    {
        try {
            // задаем данные из строки импорта
            foreach ($this->getFields() as $oField) {
                $oField->loadData($aBuffer);
            }
        } catch (\Exception $e) {
            $this->logger->setListParam('error_list', $e->getMessage());
            $this->skipCurrentRow(true);
        }
    }

    /**
     * Действие перед обработкой полей.
     */
    private function beforeExecuteFields()
    {
        try {
            // проводим операции перед обработкой данных
            foreach ($this->getFields() as $oField) {
                $oField->beforeExecute();
            }
        } catch (\Exception $e) {
            $this->logger->setListParam('error_list', $e->getMessage());
            $this->skipCurrentRow(true);
        }
    }

    /**
     * Действие перед сохранением полей.
     */
    private function beforeSaveFields()
    {
        try {
            // проводим операции перед обработкой данных
            foreach ($this->getFields() as $oField) {
                $oField->beforeSave();
            }
        } catch (\Exception $e) {
            $this->logger->setListParam('error_list', $e->getMessage());
            $this->skipCurrentRow(true);
        }
    }

    /**
     * Действие после сохранений полей.
     */
    private function afterSaveFields()
    {
        try {
            // проводим операции перед обработкой данных
            foreach ($this->getFields() as $oField) {
                $oField->afterSave();
            }
        } catch (\Exception $e) {
            $this->logger->setListParam('error_list', $e->getMessage());
        }
    }

    /**
     * Собираем данные с процессоров полей.
     */
    private function executeFields()
    {
        try {
            // собираем данные с процессоров полей
            foreach ($this->getFields() as $oField) {
                $oField->execute();
            }
        } catch (\Exception $e) {
            $this->logger->setListParam('error_list', $e->getMessage());
            $this->skipCurrentRow(true);
        }
    }

    /**
     * Сохранение записи товара.
     */
    private function saveGoodsRow()
    {
        if ($this->goodsRow) {
            $aData = $this->goodsRow->getData();

            $aData[self::$sUpdatedFieldName] = 1;

            $this->goodsRow->setData($aData);

            // сохраняем запись
            $new = $this->goodsRow->save();
            $title = $this->goodsRow->getData()['title'];

            if ($new) {
                if ($this->config->getParam('new')) {
                    //добавлен
                    $this->logger->incParam('new');
                    $this->logger->setListParam('new_list', $title);
                } else {
                    //обновлен
                    $this->logger->incParam('update');
                    $this->logger->setListParam('update_list', $title);
                }
            } else {
                $this->logger->incParam('error');
                $this->logger->setListParam('error_list', $this->getErrorSaveGoodsRow());
            }
        } else {
            $this->logger->incParam('error');
        }
    }

    /**
     * Пропуск строки.
     */
    private function skip()
    {
        $this->logger->incParam('skip');
        $this->logger->setListParam('skip_list', $this->getSkipTitleText());
    }

    /**
     * Отдает набор объектов процессоров полей.
     *
     * @return field\Prototype[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Устанавливает флаг для пропуска текущей строки.
     *
     * @param bool $bSkip
     */
    public function skipCurrentRow($bSkip = true)
    {
        $this->skipCurrentRow = $bSkip;
    }

    /**
     * Возвращает карточку провайдера.
     *
     * @return string
     */
    public function getCard()
    {
        return $this->cardName;
    }

    /**
     * Конфиг задачи.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Логгер
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Данное сообщение будет показано пользователю,
     * если была попытка запуска импорта, но мьютекс(локальный или глобальный) был занят
     */
    public function getUserMessageOnMutexBusy()
    {
        return \Yii::t('import', 'prev_import_not_complete');
    }

    /**
     * Получить конфиг задачи.
     *
     * @param ImportTemplateRow $oImportTemplate - шаблон импорта
     *
     * @return array
     */
    public static function getConfigTask(ImportTemplateRow $oImportTemplate)
    {
        return [
            'title' => \Yii::t('import', 'task_title', $oImportTemplate->title),
            'class' => self::className(),
            'priority' => self::priorityHigh,
            'resource_use' => self::weightHigh,
            'parameters' => ['tpl' => (int) $oImportTemplate->id],
            'send_error' => $oImportTemplate->send_error,
        ];
    }

    /**
     * Устанавливает флаг необходимости удаления товара
     * @param bool $bDelete
     */
    public function deleteCurrentGood($bDelete = true)
    {
        $this->deleteCurrentGood = $bDelete;
    }

    /**
     * Удаление товара
     */
    private function deleteGood()
    {
        CatalogApi::deleteGoods($this->goodsRow->getRowId());

        $this->logger->incParam('delete');

        $title = $this->config->getParam('current_title');
        if ($title) {
            $this->logger->setListParam('delete_list', $title);
        }
    }

    private function sendMailAdminAboutCollections($aIdsCollections)
    {
        $sOutLetter = '';
        foreach ($aIdsCollections as $name => $id) {
            $aCollection = catalog\Collection::getCollection($name);

            $oTable = \skewer\base\ft\Cache::getMagicTable($name);
            $aItems = $oTable->find()->where('id >=?', $id)->asArray()->getAll();
            $sLetter = '';
            if (count($aItems) > 0) {
                $sLetter = str_replace(
                    ['[title_collection]'],
                    [($aCollection) ? $aCollection->title : $name],
                    \Yii::t('collections', 'letter_admin')
                );

                foreach ($aItems as $item) {
                    $sLetter .= '<b>' . $item['title'] . '</b><br/>';
                }
                $sLetter .= '<br/><br/>';
            }

            if ($sLetter != '') {
                $sOutLetter .= $sLetter;
            }
        }
        $sOutLetter .= \Yii::t('collections', 'letter_admin_link') . '<br/>' . Site::admUrl('Collections', 'catalog');

        Mailer::sendMailAdmin(\Yii::t('collections', 'title_mail_new_element'), $sOutLetter);
    }

    /**
     * @param $iTask
     * @param $aConfig
     *
     * @throws UserException
     *
     * @return array|bool|queue\ar\TaskRow|\skewer\base\orm\ActiveRecord
     */
    public static function checkTask($iTask, $aConfig)
    {
        if ($iTask) {
            $oTaskRow = queue\ar\Task::findOne(['id' => $iTask]);
            if ($oTaskRow instanceof queue\ar\TaskRow) {

                return $oTaskRow;
            }
        }

        $oTaskRow = queue\Api::getTaskInProgress($aConfig);
        if ($oTaskRow instanceof queue\ar\TaskRow) {
            throw new UserException(\Yii::t('import', 'err_task_in_progress', [$oTaskRow->id]));
        }

        throw new UserException(\Yii::t('import', 'err_task_not_created', [$aConfig['class'], $iTask]));
    }

    /**
     * @return mixed|string
     */
    private function getSkipTitleText()
    {
        $title = $this->config->getParam('current_title');
        if ($title) {
            return $title;
        }

        $sUniqueValue = $this->config->getParam('unique_value', null);
        if ($sUniqueValue !== null) {
            return \Yii::t('import', 'skip_empty_title_unique', [$sUniqueValue]);
        }

        return \Yii::t('import', 'skip_empty_title');
    }

    /**
     * @return string
     */
    private function getErrorSaveGoodsRow(): string
    {
        $sUniqueValue = $this->config->getParam('unique_value');
        $sTitleGoods = $this->goodsRow->getData()['title'];
        $sTitleField = '';

        $aErrorList = $this->goodsRow->getErrorList();
        $sError = reset($aErrorList);
        $sNameField = key($aErrorList);

        $oField = $this->goodsRow->getBaseRow()->getModel()->getFiled($sNameField);
        if ($oField) {
            $sTitleField = $oField->getTitle();
        }

        return "$sTitleGoods($sUniqueValue): $sTitleField - $sError";
    }
}
