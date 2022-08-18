<?php

namespace skewer\build\Tool\ImportContent;

use skewer\base\queue;
use skewer\base\section\Tree;
use skewer\base\ui\ARSaveException;
use skewer\base\ui\ORMSaveException;
use skewer\build\Adm\GuestBook\models;
use skewer\build\Adm\News;
use skewer\build\Page\Articles\Model\Articles;
use skewer\components\import;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\Config;
use skewer\components\import\Task;
use skewer\components\seo;
use yii\helpers\ArrayHelper;

/**
 * Задача импортирования разделов и их seo данных
 * Class Task.
 */
class ImportTask extends queue\Task
{
    /** @var string Режим работы импорта - создание разделов */
    public static $sCreateMode = 'create';

    /** @var string Режим работы импорта - обновление разделов */
    public static $sUpdateMode = 'update';

    /** @var \skewer\components\import\Logger */
    public $oLogger;

    /** @var Config */
    public $oConfig;

    /** @var XlsProvider */
    public $oProvider;

    /**
     * @return bool|void
     */
    public function init()
    {
        $aArgs = func_get_args();

        $sFilePath = ArrayHelper::getValue($aArgs[0], 'file', '');

        $oTemplate = self::getImportTemplate($sFilePath);

        $this->oConfig = new Config($oTemplate);
        $this->oConfig->setParam('skip_row', 1);
        $this->oProvider = new XlsProvider($this->oConfig);

        $this->oLogger = new import\Logger($this->getId(), $oTemplate->id);

        $this->oLogger->setParam('start', date('Y-m-d H:i:s'));
        $this->oLogger->setParam('newRecords', 0);
        $this->oLogger->setParam('updateRecords', 0);
        $this->oProvider->setConfigVal('file', ArrayHelper::getValue($aArgs, '0.file', ''));

        $sDataType = ArrayHelper::getValue($aArgs, '0.data_type', '');
        $this->oProvider->setConfigVal('sDataType', $sDataType);

        return true;
    }

    /**
     * @throws \Exception
     */
    public function recovery()
    {
        $aArgs = func_get_args();

        if (!isset($aArgs[0]['data'])) {
            throw new \Exception('no valid data');
        }
        $aData = json_decode($aArgs[0]['data'], true);

        $oTemplate = self::getImportTemplate($aData['file']);

        $this->oConfig = new Config($oTemplate);
        $this->oConfig->setParam('skip_row', 1);
        $this->oConfig->setData($aData);

        $this->oProvider = new XlsProvider($this->oConfig);
        $this->oLogger = new import\Logger($this->getId(), $oTemplate->id);
    }

    /** Заморозка задачи */
    public function reservation()
    {
        $this->setParams(['data' => $this->oConfig->getJsonData()]);
        $this->oLogger->setParam('status', static::stFrozen);
        $this->oLogger->save();
    }

    public function beforeExecute()
    {
        $this->oProvider->beforeExecute();
    }

    /**
     * @return bool
     */
    public function execute()
    {
        /* Если провайдер не разрешает читать - прерываемся */
        if (!$this->oProvider->canRead()) {
            $this->setStatus(static::stInterapt);

            return false;
        }

        /** Получение данных */
        $aBuffer = $this->oProvider->getRow();

        /* Данных нет - завершаем импорт */
        if ($aBuffer === false) {
            $this->setStatus(static::stComplete);
            $this->oConfig->setParam('importStatus', Task::importFinish);

            return true;
        }

        if ($aBuffer = $this->validateData($aBuffer)) {
            $this->saveRow($aBuffer);
        }

        return true;
    }

    /** Набор полей, определяющий шаблон файла импорта
     *  Одинаковый для режима обновления и добавления.
     *
     * @param $sType - тип импортируемых данных
     *
     * @return array
     */
    public static function getFields($sType)
    {
        switch ($sType) {
            case Api::DATATYPE_NEWS:
                return ['section', 'news_alias', 'name', 'announce', 'publication_date', 'full_text', 'archive', 'title', 'description', 'keywords'];
            case Api::DATATYPE_REVIEWS:
                return ['section', 'date_time', 'name', 'email', 'content', 'status', 'city', 'rating'];
            case Api::DATATYPE_ARTICLES:
                return ['section', 'articles_alias', 'name', 'announce', 'publication_date', 'full_text', 'author', 'archive', 'title', 'description', 'keywords'];
            default:
                return [];
        }
    }

    /**
     * @param $aBuffer
     */
    public function saveRow($aBuffer)
    {
        $sDataType = $this->oProvider->getConfigVal('sDataType');

        $aErrors = [];

        try {
            switch ($sDataType) {
                case Api::DATATYPE_NEWS:

                    $oNewsRow = News\models\News::getNewRow();
                    $aBufferNews = $aBuffer;
                    $aBufferNews['title'] = $aBufferNews['name'];
                    $oNewsRow->setAttributes($aBufferNews);
                    $oNewsRow->parent_section = Tree::getSectionByPath($aBuffer['section']);
                    $aBuffer['alias_record'] = $aBuffer['section'] . $aBuffer['news_alias'];
                    $aBuffer['type'] = News\Seo::className();
                    $bRes = $oNewsRow->save();

                    if (!$bRes) {
                        throw new ARSaveException($oNewsRow);
                    }
                    $this->updateRecord($aBuffer, $aErrors);
                    break;
                case Api::DATATYPE_REVIEWS:

                    $oReviewsRow = models\GuestBook::getNewRow();
                    $oReviewsRow->setAttributes($aBuffer);
                    $oReviewsRow->parent = Tree::getSectionByPath($aBuffer['section']);

                    if (!$oReviewsRow->save()) {
                        throw new ARSaveException($oReviewsRow);
                    }

                    break;
                case Api::DATATYPE_ARTICLES:

                    $oArticlesRow = Articles::getNewRow();
                    $aBufferArticles = $aBuffer;
                    $aBufferArticles['title'] = $aBufferArticles['name'];
                    $oArticlesRow->setData($aBufferArticles);
                    $oArticlesRow->parent_section = Tree::getSectionByPath($aBuffer['section']);
                    $aBuffer['alias_record'] = $aBuffer['section'] . $aBuffer['articles_alias'];
                    $aBuffer['type'] = \skewer\build\Adm\Articles\Seo::className();
                    if (!$oArticlesRow->save()) {
                        throw new ORMSaveException($oArticlesRow);
                    }
                    $this->updateRecord($aBuffer, $aErrors);
                    break;
            }
        } catch (\Exception $e) {
            $aErrors[] = $e->getMessage();
        }

        if (!$aErrors) {
            $this->oLogger->incParam('newRecords');
        } else {
            $this->oLogger->setListParam('notAdded', 'Строка №' . ($this->oProvider->getConfigVal('row') - 1) . '  ' . array_shift($aErrors));
        }
    }

    public function complete()
    {
        $this->oLogger->setParam('finish', date('Y-m-d H:i:s'));
        $this->oLogger->save();

        // Ставим задачу на обновление Sitemap
        seo\Service::updateSiteMap();
    }

    /** Обновление записи
     * @param $aBuffer - массив с данными
     * @param $aError  - массив ошибок
     *
     * @return bool
     */
    private function updateRecord($aBuffer, &$aError)
    {
        try {
            if (($iRecordId = self::doExistRecord($aBuffer['type'], $aBuffer['alias_record'])) === false) {
                throw new \Exception('Запись не существует');
            }
            $iSectionId = Tree::getSectionByPath($aBuffer['alias_record']);

            self::updateEntity($aBuffer['type'], $iRecordId, $iSectionId, $aBuffer);
        } catch (\Exception $e) {
            $aError[] = $e->getMessage();
        }
    }

    /** Имя класса */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Шаблон экспорта. Нужен для использования Logger и Config.
     *
     * @param $sFile - Имя файла
     *
     * @return ImportTemplateRow|\skewer\base\orm\ActiveRecord
     */
    public static function getImportTemplate($sFile)
    {
        return ImportTemplate::getNewRow([
            'title' => 'Шаблон импорта контента',
            'type' => import\Api::Type_File,
            'provider_type' => import\Api::ptXLS,
            'source' => $sFile,
        ]);
    }

    /**
     * Возвращает конфиг текущей задачи.
     *
     * @param mixed $aParams
     *
     * @return array
     */
    public static function getConfig($aParams = [])
    {
        return [
            'title' => 'Импорт контента',
            'name' => 'importContent',
            'class' => self::className(),
            'parameters' => $aParams,
            'priority' => ImportTask::priorityLow,
            'resource_use' => ImportTask::weightLow,
            'target_area' => 1, // область применения - площадка
        ];
    }

    /**
     * Метод проверки существования записи.
     * Вернёт id записи или false если запись не найдена.
     *
     * @param $sEntityType   - тип сущности
     * @param $sAliasRecord  - alias записи
     *
     * @return bool|int
     */
    public static function doExistRecord($sEntityType, $sAliasRecord)
    {
        /** @var seo\SeoPrototype $oSeo */
        if (!class_exists($sEntityType) || !(($oSeo = new $sEntityType()) instanceof seo\SeoPrototype)) {
            return false;
        }

        return $oSeo->doExistRecord($sAliasRecord);
    }

    /**
     * Метод обновления данных сущности.
     *
     * @param $iEntityType - тип сущности
     * @param $iEntityId   - id сущности
     * @param $iSectionId  - id раздела
     * @param $aData       - данные
     *
     * @return bool        - true - успешное обновление, false - запись не обновлена
     */
    public static function updateEntity($iEntityType, $iEntityId, $iSectionId, $aData)
    {
        if (!class_exists($iEntityType)) {
            return false;
        }

        /** @var seo\SeoPrototype $oSeo */
        $oSeo = new $iEntityType();
        if (!($oSeo instanceof seo\SeoPrototype)) {
            return false;
        }

        $oSeo->setEntityId($iEntityId);
        $oSeo->setSectionId($iSectionId);
        $oSeo->loadDataEntity();
        $oSeo->initSeoData();

        $aSeoData = [];
        foreach (seo\SeoPrototype::getField4Parsing() as $item) {
            if (isset($aData[$item]) && (seo\Api::prepareRawString($oSeo->parseField($item)) !== seo\Api::prepareRawString($aData[$item]))) {
                $aSeoData[$item] = seo\Api::prepareRawString($aData[$item]);
            }
        }

        seo\Api::set($oSeo::getGroup(), $iEntityId, $iSectionId, $aSeoData);

        return true;
    }

    /**
     * Проверяет корректность считанных данных.
     *
     * @param $aData
     *
     * @return array|bool Вернет преобразованный к нужному ввиду массив или false если данные некорректны
     */
    public function validateData($aData)
    {
        $sDataType = $this->oProvider->getConfigVal('sDataType', '');

        $aData = array_slice($aData, 0, count(self::getFields($sDataType)));
        $aData = array_combine(self::getFields($sDataType), $aData);

        return $aData;
    }
}
