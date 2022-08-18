<?php

namespace skewer\components\excelHelpers;

/**
 * Class ReadHelper.
 */
class ReadHelper
{
    /**
     * Открывает файл на чтение.
     *
     * @param $sFilePath - путь к файлу
     * @param null|\PHPExcel_Reader_IReadFilter $oReadFilter - фильтр, ограничивающий набор ячеек загружаемых в память
     * @param array $aLoadSheetsOnly - массив листов(вкладок), которые будут загружены
     *
     * @return \PHPExcel
     */
    public static function createReaderForFile($sFilePath, \PHPExcel_Reader_IReadFilter $oReadFilter = null, $aLoadSheetsOnly = [])
    {
        $objReader = \PHPExcel_IOFactory::createReaderForFile($sFilePath);

        $objReader->setReadDataOnly(true);

        if ($oReadFilter) {
            $objReader->setReadFilter($oReadFilter);
        }

        if ($aLoadSheetsOnly) {
            $objReader->setLoadSheetsOnly($aLoadSheetsOnly);
        }

        $objPHPExcel = $objReader->load($sFilePath);

        return $objPHPExcel;
    }
}
