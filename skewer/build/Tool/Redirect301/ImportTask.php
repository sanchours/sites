<?php

namespace skewer\build\Tool\Redirect301;

use skewer\base\queue;
use skewer\build\Tool\ImportContent\XlsProvider;
use skewer\components\import;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\Config;
use skewer\components\import\Task;
use skewer\components\redirect\models\Redirect;
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
        $this->oConfig->setParam('row_count', 2);
        $this->oProvider = new XlsProvider($this->oConfig);

        $this->oLogger = new import\Logger($this->getId(), $oTemplate->id);

        $this->oLogger->setParam('start', date('Y-m-d H:i:s'));
        $this->oLogger->setParam('newRecords', 0);
        $this->oProvider->setConfigVal('file', ArrayHelper::getValue($aArgs, '0.file', ''));

        $sDataType = ArrayHelper::getValue($aArgs, '0.data_type', '');
        $this->oProvider->setConfigVal('sDataType', $sDataType);

        //удаляем редиректы
        Redirect::deleteAll();
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
        $this->oConfig->setParam('row_count', 2);
        $this->oConfig->setData($aData);

        $this->oProvider = import\Api::getProvider($this->oConfig);
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

        $aBuffer = $this->indexData($aBuffer);
        $this->saveRow($aBuffer);

        return true;
    }

    public function complete()
    {
        $this->oLogger->setParam('status', static::stComplete);
        $this->oLogger->setParam('finish', date('Y-m-d H:i:s'));
        $this->oLogger->save();
    }

    /**
     * @param $aBuffer
     */
    public function saveRow($aBuffer)
    {
        $aInData = \skewer\build\Tool\Redirect301\Api::prepareRedirect($aBuffer);

        $oRedirect = new Redirect();
        $oRedirect->setAttributes($aInData);

        $oRedirect->save();
        $this->oLogger->incParam('newRecords');
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
            'title' => 'Шаблон импорта списка редиректов',
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
            'title' => 'Импорт списка редиректов',
            'name' => 'redirect301',
            'class' => self::className(),
            'parameters' => $aParams,
            'priority' => ImportTask::priorityLow,
            'resource_use' => ImportTask::weightLow,
            'target_area' => 1, // область применения - площадка
        ];
    }

    public function indexData($aData)
    {
        $aNewData = ['new_url' => $aData[1], 'old_url' => $aData[0]];

        return $aNewData;
    }
}
