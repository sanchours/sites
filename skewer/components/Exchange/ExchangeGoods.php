<?php

namespace skewer\components\Exchange;

use skewer\base\log\Logger;
use skewer\base\queue;
use skewer\base\site_module\Request;
use skewer\base\SysVar;
use skewer\components\import;
use skewer\components\import\Api;
use skewer\components\import\ar;
use skewer\components\import\Config;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class ExchangeGoods.
 *
 * @see http://v8.1c.ru/edi/edi_stnd/131/ - Протокол обмена между системой "1С:Предприятие" и сайтом
 */
class ExchangeGoods extends ExchangePrototype
{
    /**
     * Прием файлов от 1с
     */
    protected function cmdFile()
    {
        if ($bRes = $this->loadFileImport()) {
            $sResponse = 'success';
        } else {
            $sResponse = 'failure';
        }

        $this->sendResponse($sResponse);
    }

    /**
     * Импорт товаров и цен.
     */
    protected function cmdImport()
    {
        $aRes = $this->importGoodsAndPrices();

        if ($aRes === false) {
            $sResponse = "failure\n" . iconv('utf-8', 'windows-1251', 'Ошибка запуска задачи');
        } elseif ($aRes['repeat']) {
            $sResponse = "progress\n" . iconv('utf-8', 'windows-1251', $aRes['message']);
        } else {
            $sResponse = 'success';
        }

        $this->sendResponse($sResponse);
    }

    /**
     * Импорт товаров и цен.
     *
     * @return array | bool
     */
    private function importGoodsAndPrices()
    {
        $iCountIteration = SysVar::get('1c.counterIteration', 0);

        // Инициализация параметров до выполнения импорта
        if ($iCountIteration == 0) {
            $this->startImport();
        }

        SysVar::set('1c.counterIteration', ++$iCountIteration);

        // Сообщение статуса выполнения операции для 1с
        $sMessageStatus = '';

        // Флаг необходимости повторить этот же запрос
        $bNeedRepeatRequest = false;

        // Результат работы метода
        $aOut = ['repeat' => &$bNeedRepeatRequest, 'message' => &$sMessageStatus];

        // флаг, указывающий на то, что импорт товаров и предложений выполнен
        $bImportCompleted = SysVar::get('1c.bImportCompleted', false);

        $sFileName = Request::getStr('filename');

        if (mb_strpos($sFileName, 'import') !== false) {
            $sMode = 'import';
        } elseif (mb_strpos($sFileName, 'offers') !== false) {
            $sMode = 'offers';
        } else {
            return $aOut;
        }

        $aCurrentTask = [];

        if (!$bImportCompleted) {
            // 1. Проверка наличия необходимых шаблонов импорта
            /** @var ar\ImportTemplateRow $oImportTemplate */
            if (!$oImportTemplate = self::getImportTemplateFor1c($sMode)) {
                return $aOut;
            }

            // 2. Установка путей к файлам импорта
            $sFullNameImportFile = $this->getFullNameCurrentFile($sFileName);
            if ($this->validateXmlFile($sFullNameImportFile) === false) {
                return false;
            }
            self::changeImportTemplate($oImportTemplate, $sFullNameImportFile);

            $aCurrentTask = ($sMode == 'import')
                ? self::settingTaskImportGood()
                : self::settingTaskImportOffers();
        } else {
            $aCurrentTask = self::settingTaskDeleteFiles();
        }

        try {
            $aRes = queue\Task::runTask($aCurrentTask['configTask'], 0, true);

            //восстанавливаем шаблон импорта
            self::recoveryImportTemplate();
        } catch (\Exception $e) {
            self::recoveryImportTemplate();
            $this->recoveryDefaultVar();
            Logger::dump('Не запустилась задача');
            Logger::dump($aCurrentTask);
            Logger::dump($e->getMessage());

            return false;
        }

        if (in_array($aRes['status'], [queue\Task::stFrozen, queue\Task::stWait])) {
            $sMessageStatus = ArrayHelper::getValue($aCurrentTask, 'header', 'Importing in Progress');
            $bNeedRepeatRequest = true;
        } else {
            if ($bImportCompleted) {
                $this->finishImport();
            } elseif ($sMode == 'offers') {
                $aMatch = [];
                preg_match('{offers(\\d*).xml}', $sFileName, $aMatch);

                $iIndex = (int) ($aMatch[1] ?? 0);

                $iIndexLastOffersFile = count(FileHelper::findFiles($this->getDirCurrentExchange(), ['only' => ['offers*.xml']])) - 1;

                // Если загружен последний файл offers, то импорт товаров/предложений считаем оконченным
                if ($iIndex == $iIndexLastOffersFile) {
                    SysVar::set('1c.bImportCompleted', true);
                    $bNeedRepeatRequest = true;
                }
            }
        }

        return $aOut;
    }

    /**
     * Проверка на целостность файла импорта.
     *
     * @param string $filePath
     *
     * @return bool
     */
    private function validateXmlFile(string $filePath): bool
    {
        $xml = new \DOMDocument();
        $result = @$xml->load($filePath);

        if ($result === false) {
            Logger::dump("Файл {$filePath} не прошёл проверку на целостность.");

            return false;
        }

        return true;
    }

    /** Старт импорта. Инициализация параметров */
    private function startImport()
    {
        SysVar::set('1c.bImportCompleted', false);
    }

    /** Конец импорта. Восстановление параметров */
    private function finishImport()
    {
        // сбрасываем счетчик итераций
        SysVar::set('1c.counterIteration', 0);
    }

    /**
     * Получить шаблон импорта для обмена с 1с
     *
     * @param string $type режим обмена
     *
     * @return bool|\skewer\base\orm\ActiveRecord
     */
    private static function getImportTemplateFor1c($type)
    {
        if ($type == 'import') {
            $sParamName = '1c.id_import_template_goods';
        } elseif ($type == 'offers') {
            $sParamName = '1c.id_import_template_prices';
        } else {
            return false;
        }

        if (!($iId = SysVar::get($sParamName))) {
            return false;
        }

        return  ar\ImportTemplate::find($iId);
    }

    /**
     * Изменяет шаблон импорта: устанавливает пути к файлам импорта(к самому файлу импорта + к директории с изображениями).
     *
     * @param ar\ImportTemplateRow $oImportTemplate - шаблон импорта
     * @param string $sFullNameImportFile - полный путь к файлу импорта
     */
    private static function changeImportTemplate(ar\ImportTemplateRow $oImportTemplate, $sFullNameImportFile)
    {
        self::setOriginImportTemplate($oImportTemplate);

        // Подмена пути к директории с изображениями
        $oConfig = new Config($oImportTemplate);
        $oConfig->setFieldsParam(['params_gallery:imagedir' => str_replace(ROOTPATH, '', dirname($sFullNameImportFile))]);
        $oImportTemplate->settings = $oConfig->getJsonData();

        // Подмена пути к файлу обмена
        $oImportTemplate->source = str_replace(ROOTPATH, '', $sFullNameImportFile);
        $oImportTemplate->type = Api::Type_Path;
        $oImportTemplate->save();
    }

    /** Восстанавливает шаблон импорта в исходное состояние(до выполнения автоматического обмена) */
    private static function recoveryImportTemplate()
    {
        /** @var ar\ImportTemplateRow $oImportTemplate */
        $oImportTemplate = self::getOriginImportTemplate();

        if ($oImportTemplate) {
            $oImportTemplate->save();
        }
    }

    /**
     * Сохраняет исходный шаблон импорта.
     *
     * @param ar\ImportTemplateRow $oImportTemplate
     */
    private static function setOriginImportTemplate(ar\ImportTemplateRow $oImportTemplate)
    {
        SysVar::set('1c.origin_import_template', serialize($oImportTemplate));
    }

    /**
     * Вернет исходный(до выполнения автомат.импорта) шаблон импорта.
     *
     * @return ar\ImportTemplate | false
     */
    private static function getOriginImportTemplate()
    {
        $sSerializeObj = SysVar::get('1c.origin_import_template', null);

        if (!$sSerializeObj) {
            return false;
        }

        return unserialize($sSerializeObj);
    }

    /**
     * Конфигурация задачи "Импорт товаров".
     *
     * @return array|bool
     */
    public static function settingTaskImportGood()
    {
        /** @var ar\ImportTemplateRow $oImportTpl */
        if (!$oImportTpl = self::getImportTemplateFor1c('import')) {
            return false;
        }

        return [
            'header' => 'Импорт товаров в CMS',
            'configTask' => import\Task::getConfigTask($oImportTpl),
        ];
    }

    /**
     * Конфигурация задачи "Импорт предложений".
     *
     * @return array|bool
     */
    public static function settingTaskImportOffers()
    {
        /** @var ar\ImportTemplateRow $oImportTpl */
        if (!$oImportTpl = self::getImportTemplateFor1c('offers')) {
            return false;
        }

        return [
            'header' => 'Импорт предложений в CMS',
            'configTask' => import\Task::getConfigTask($oImportTpl),
        ];
    }

    /**
     * Конфигурация задачи "Удаление файлов 1с".
     *
     * @return array
     */
    public static function settingTaskDeleteFiles()
    {
        return [
           'header' => 'Удаление файлов обмена',
           'configTask' => DeleteFileTask::getConfig(),
        ];
    }

    /** Восстановление дефолтных настроек */
    private function recoveryDefaultVar()
    {
        SysVar::set('1c.counterIteration', 0);
        SysVar::set('1c.bImportCompleted', true);
    }
}
