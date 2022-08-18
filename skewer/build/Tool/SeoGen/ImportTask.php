<?php

namespace skewer\build\Tool\SeoGen;

use skewer\base\queue;
use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\build\Tool\SeoGen\importer\Prototype;
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
    /** @var \skewer\components\import\Logger */
    public $oLogger;

    /** @var Config */
    public $oConfig;

    /** @var XlsWriter */
    public $oProvider;

    /** @var Prototype */
    public $oImporter;

    /**
     * Возвращает конфиг текущей задачи.
     *
     * @param array $aParams
     *
     * @return array
     */
    public static function getConfig($aParams = [])
    {
        return [
            'title' => 'Импорт разделов(seo)',
            'name' => 'importSeo',
            'class' => self::className(),
            'parameters' => $aParams,
            'priority' => ImportTask::priorityLow,
            'resource_use' => ImportTask::weightLow,
            'target_area' => 1,
            // область применения - площадка
        ];
    }

    /** Имя класса */
    public static function className()
    {
        return get_called_class();
    }

    public function init()
    {
        $aArgs = func_get_args();

        $sFilePath = ArrayHelper::getValue($aArgs[0], 'file', '');

        $oTemplate = self::getImportTemplate($sFilePath);

        $this->oConfig = new Config($oTemplate);

        foreach ($this->getParameters() as $sParamName => $mParamValue) {
            $this->oConfig->setParam($sParamName, $mParamValue);
        }

        $this->oConfig->setParam('skip_row', 1);
        $this->oProvider = import\Api::getProvider($this->oConfig);

        $this->oLogger = new import\Logger($this->getId(), $oTemplate->id);

        $this->oLogger->setParam('start', date('Y-m-d H:i:s'));
        $this->oLogger->setParam('type_operation', 'import');
        $this->oLogger->setParam('newRecords', 0);
        $this->oLogger->setParam('updateRecords', 0);
        $this->oProvider->setConfigVal('file', ArrayHelper::getValue($aArgs, '0.file', ''));

        $sDataType = ArrayHelper::getValue($aArgs, '0.data_type', '');
        $this->oProvider->setConfigVal('sDataType', $sDataType);

        $this->oImporter = ImporterApi::getImporterByAlias($sDataType);
        $this->oImporter->initParams($aArgs[0]);
        $this->oImporter->beforeInitImport();

        return true;
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
            'title' => 'Шаблон импорта seo данных',
            'type' => import\Api::Type_File,
            'provider_type' => import\Api::ptXLS,
            'source' => $sFile,
        ]);
    }

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

        $this->oProvider = import\Api::getProvider($this->oConfig);
        $this->oLogger = new import\Logger($this->getId(), $oTemplate->id);

        $this->oImporter = ImporterApi::getImporterByAlias($aData['sDataType']);
        $this->oImporter->initParams($aData);
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
            // Пропустить строку?
            if ($this->doSkipRow($aBuffer)) {
                return true;
            }

            $this->saveRow($aBuffer);
        }

        return true;
    }

    /**
     * Пропустить строку?
     *
     * @param array
     * @param mixed $aData
     *
     * @return bool
     */
    public function doSkipRow($aData)
    {
        return $this->oImporter->doSkipRow($aData);
    }

    /**
     * Проверяет корректность считанных данных.
     *
     * @param $aData
     *
     * @return array|bool Вернет преобразованный к нужному ввиду массив или false если данные некорректны
     */
    protected function validateData($aData)
    {
        $aErrors = [];

        $aData = $this->oImporter->sliceData($aData);

        $this->oImporter->validateData($aData, $aErrors);

        if ($aErrors) {
            foreach ($aErrors as $sError) {
                $this->oLogger->setListParam('error_list', sprintf('Строка №%d  %s', $this->getCurrentRowIndex(), $sError));
            }

            return false;
        }

        return $aData;
    }

    /**
     * @return int|mixed
     */
    protected function getCurrentRowIndex()
    {
        return $this->oProvider->getConfigVal('row') - 1;
    }

    protected function saveRow($aBuffer)
    {
        $aErrors = [];

        $bResCode = $this->oImporter->saveRow($aBuffer, $aErrors);

        switch ($bResCode) {
            case Prototype::ADDED_STATUS:
                $this->oLogger->incParam('newRecords');
                break;

            case Prototype::NOT_ADDED_STATUS:
                $this->oLogger->setListParam('notAdded', 'Строка №' . $this->getCurrentRowIndex() . '  ' . array_shift($aErrors));
                break;

            case Prototype::UPDATE_STATUS:
                $this->oLogger->incParam('updateRecords');
                break;

            case Prototype::NOT_UPDATE_STATUS:
                $this->oLogger->setListParam('notUpdated', 'Строка №' . $this->getCurrentRowIndex() . '  ' . array_shift($aErrors));
                break;

            default:
                throw new \Exception('Неизвестный код результата сохранения');
        }
    }

    public function afterExecute()
    {
        foreach ($this->oImporter->getParams4Save() as $sKey => $item) {
            $this->oProvider->setConfigVal($sKey, $item);
        }
    }

    public function complete()
    {
        $this->oLogger->setParam('finish', date('Y-m-d H:i:s'));
        $this->oLogger->setParam('status', static::stComplete);
        $this->oLogger->save();

        // Ставим задачу на обновление Sitemap
        seo\Service::updateSiteMap();
    }
}
