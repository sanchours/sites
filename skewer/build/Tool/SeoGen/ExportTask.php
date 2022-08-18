<?php

namespace skewer\build\Tool\SeoGen;

use skewer\base\queue;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\build\Adm\Tree\Exporter;
use skewer\build\Tool\SeoGen\exporter\Api as ExporterApi;
use skewer\build\Tool\SeoGen\exporter\Prototype;
use skewer\components\excelHelpers\WriteHelper;
use skewer\components\import;
use skewer\helpers\Files;

class ExportTask extends queue\Task
{
    /** @var import\Config */
    public $oConfig;

    /** @var \skewer\components\import\Logger */
    public $oLogger;

    /** @var XlsWriter */
    public $oProvider;

    /** @var Prototype */
    public $oExporter;

    /** Инициализация задачи */
    public function init()
    {
        $aArgs = func_get_args();

        try {
            $oTemplate = self::getImportTemplate();

            $this->oLogger = new import\Logger($this->getId(), $oTemplate);
            $this->oLogger->setParam('start', date('Y-m-d H:i:s'));
            $this->oLogger->setParam('type_operation', 'export');

            $this->oConfig = new import\Config($oTemplate);

            $sSeoDir = PRIVATE_FILEPATH . Api::SEO_DIRECTORY . \DIRECTORY_SEPARATOR;

            Files::createFolderPath(Api::SEO_DIRECTORY, true);

            if (!file_exists(Api::getSystemPathExportFile())) {
                $oExcel = WriteHelper::createNewWorkBook();
                WriteHelper::save($oExcel, Api::getSystemPathExportFile());
            }

            $oProvider = import\Api::getProvider($this->oConfig);
            if ($oProvider instanceof import\provider\Xls) {
                $this->oProvider = new XlsWriter($this->oConfig);
            }

            if (!isset($aArgs[0]['data_type'])) {
                throw new \Exception('Неверный тип данных');
            }

            $sDataType = $aArgs[0]['data_type'];

            $this->setConfigVal('sDataType', $aArgs[0]['data_type']);
            $this->initConfigData($aArgs[0]);
            $this->oExporter = ExporterApi::getExporterByAlias($sDataType);
            $this->oExporter->initParams($aArgs[0]);

            $aHeaderFields = array_values($this->oExporter->fields4Export());
            $this->oProvider->createXlsFile($aHeaderFields);
            $this->oLogger->save();
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function initConfigData($aData, $aExcludedParams = ['data_type'])
    {
        foreach ($aData as $sKey => $sParam) {
            if (!in_array($sKey, $aExcludedParams)) {
                $this->setConfigVal($sKey, $sParam);
            }
        }
    }

    /** Востановление задачи */
    public function recovery()
    {
        $aArgs = func_get_args();

        if (!isset($aArgs[0]['data'])) {
            throw new \Exception('no valid data');
        }
        $aConfigData = json_decode($aArgs[0]['data'], true);

        /* Собираем конфиг */
        $this->oConfig = new import\Config();
        $this->oConfig->setData($aConfigData);

        $sDataType = $aConfigData['sDataType'];

        $this->oExporter = ExporterApi::getExporterByAlias($sDataType);
        $this->oExporter->initParams($aConfigData);

        $oProvider = import\Api::getProvider($this->oConfig);

        $this->oLogger = new import\Logger($this->getId(), self::getImportTemplate());

        if ($oProvider instanceof import\provider\Xls) {
            $this->oProvider = new XlsWriter($this->oConfig);
        }
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
        $this->oLogger->setParam('status', static::stProcess);
        $this->oProvider->beforeExecute();
    }

    /** Выполнение очередноой итерации */
    public function execute()
    {
        $iRowIndex = $this->getConfigVal('row');

        $aBuffer = $this->executeExportEntity($this->oExporter->getSourceSections());

        if ($aBuffer === false) {
            $this->setStatus(static::stComplete);

            return true;
        }

        $this->oLogger->setParam('bDataExist', true);

        $this->oProvider->writeRow($iRowIndex, $aBuffer);
        $this->setConfigVal('row', ++$iRowIndex);

        return true;
    }

    public function afterExecute()
    {
        $this->setParams(['data' => $this->oConfig->getJsonData()]);
        $this->oProvider->afterExecute();
    }

    public function complete()
    {
        $this->oLogger->setParam('finish', date('Y-m-d H:i:s'));
        $this->oLogger->setParam('status', static::stComplete);
        $this->oLogger->setParam('linkFile', Api::getWebPathExportFile());
        $this->oLogger->save();
    }

    private function fail($msg)
    {
        $this->oLogger->setListParam('error_list', $msg);
        $this->setStatus(static::stError);
    }

    public function error()
    {
        if ($this->oLogger) {
            $this->oLogger->setParam('status', static::stError);
            $this->oLogger->setParam('finish', date('Y-m-d H:i:s'));
            $this->oLogger->save();
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
     * @return import\ar\ImportTemplateRow|\skewer\base\orm\ActiveRecord
     */
    public static function getImportTemplate()
    {
        return import\ar\ImportTemplate::getNewRow([
            'title' => 'Шаблон экспорта seo данных',
            'type' => import\Api::Type_File,
            'provider_type' => import\Api::ptXLS,
            'source' => Api::getSystemPathExportFile(),
        ]);
    }

    /**
     * Возвращает конфиг текущей задачи.
     *
     * @param array $aParam - массив для передачи параметров между итерациями
     *
     * @return array
     */
    public static function getConfig($aParam = [])
    {
        return [
            'title' => 'Export разделов(seo)',
            'name' => 'ExportSeo',
            'class' => self::className(),
            'parameters' => $aParam,
            'priority' => ImportTask::priorityLow,
            'resource_use' => ImportTask::weightLow,
            'target_area' => 1, // область применения - площадка
        ];
    }

    /**
     * Сохраняет значение в конфиг по имени.
     *
     * @param string $sName имя параметра
     * @param string $sValue значение
     */
    public function setConfigVal($sName, $sValue)
    {
        $this->oProvider->setConfigVal($sName, $sValue);
    }

    /**
     * Отдает значение из конфига по имени.
     *
     * @param string $sName имя параметра (можно вложенное через .)
     * @param string $sDefault значение по умолчанию
     *
     * @return mixed
     */
    public function getConfigVal($sName, $sDefault = '')
    {
        return $this->oProvider->getConfigVal($sName, $sDefault);
    }

    /**
     * Покрасить строку данных.
     *
     * @param $aData - данные
     * @param $aStyle - массив настроек стиля
     */
    private function paint(&$aData, $aStyle)
    {
        foreach ($aData as $key => &$item) {
            if (!is_array($item)) {
                $item = [
                    'value' => $item,
                    'style' => $aStyle,
                ];
            } else {
                $item['style'] = $aStyle;
            }
        }
    }

    public function getOffsetSection()
    {
        return $this->getConfigVal('iOffsetSection', 0);
    }

    public function getOffsetEntity()
    {
        return $this->getConfigVal('iOffsetRowEntity', 0);
    }

    public function setOffsetSection($iOffsetSection)
    {
        $this->setConfigVal('iOffsetSection', $iOffsetSection);
    }

    public function setOffsetEntity($iOffsetRowEntity)
    {
        $this->setConfigVal('iOffsetRowEntity', $iOffsetRowEntity);
    }

    public function incOffsetSection()
    {
        $iOffsetSection = $this->getOffsetSection();
        $this->setOffsetSection(++$iOffsetSection);
    }

    public function incOffsetEntity()
    {
        $iOffsetRowEntity = $this->getOffsetEntity();
        $this->setOffsetEntity(++$iOffsetRowEntity);
    }

    /**
     * Итерация экспорта сущности.
     *
     * @param array $aSrcSections - разделы-источники
     *
     * @return array|bool
     */
    public function executeExportEntity($aSrcSections)
    {
        /** @var bool Флаг, завершения попыток получения данных */
        $bComplete = false;

        /** @var array Выходной массив */
        $aBuffer = false;

        while (true) {
            $iOffsetSection = $this->getOffsetSection();
            $iOffsetRowEntity = $this->getOffsetEntity();

            // Все разделы перебрали - экспорт завершён
            if (!isset($aSrcSections[$iOffsetSection])) {
                $bComplete = true;
                break;
            }

            // Скрытые из пути/скрытые от индексации разделы(вместе с вложенным контентом) не выгружаем
            $oSection = Tree::getSection($aSrcSections[$iOffsetSection]);
            if (!$oSection || !in_array($oSection->visible, Visible::$aOpenByLink)) {
                $this->incOffsetSection();
                continue;
            }

            if (!$this->oExporter->checkTemplateSection($aSrcSections[$iOffsetSection])) {
                $this->incOffsetSection();
                continue;
            }

            $aBuffer = $this->oExporter->getChunkData($aSrcSections[$iOffsetSection], $iOffsetRowEntity);

            // Данных в текущем разделе нет
            if ($aBuffer === false) {
                $this->incOffsetSection();
                $this->setOffsetEntity(0);
            } else {
                if ($this->oExporter instanceof Exporter) {
                    $this->incOffsetSection();
                    break;
                }
                $this->incOffsetEntity();
                break;
            }
        } // while

        // Экспорт завершён?
        if ($bComplete) {
            return false;
        }

        // Обрезаем лишнее
        $aBuffer = array_intersect_key(
            $aBuffer,
            $this->oExporter->fields4Export()
        );

        return $aBuffer;
    }
}
