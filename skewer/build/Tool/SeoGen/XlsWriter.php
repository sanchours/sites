<?php

namespace skewer\build\Tool\SeoGen;

use skewer\base\section\Visible;
use skewer\components\excelHelpers;
use skewer\components\import\provider\Xls;

class XlsWriter extends Xls
{
    /** @var \PHPExcel */
    protected $oExcel;

    public $skip_row = 1;

    /**
     * Физическое создание нового файла Excel.
     *
     * @param array $aHeaderFields
     */
    public function createXlsFile($aHeaderFields = [])
    {
        $this->oExcel = excelHelpers\WriteHelper::createNewWorkBook();
        $this->sheet = $this->oExcel->setActiveSheetIndex(0);

        // Строка-заголовок
        excelHelpers\WriteHelper::writeRow($this->oExcel, $aHeaderFields);

        // Устанавливаем ширину колонок
        for ($iColumnIndex = 0; $iColumnIndex < count($aHeaderFields); ++$iColumnIndex) {
            $this->sheet->getColumnDimensionByColumn($iColumnIndex)->setWidth($aHeaderFields[$iColumnIndex]['width']);
        }

        // Создаем именнованые области со списками значений, чтобы в дальнейшем создать выпадающие списки
        $this->addNamedRangeWithValuesList('ZY', 'templates', Api::getTemplatesTitle());
        $this->addNamedRangeWithValuesList('ZZ', 'visible', Visible::getVisibilityTypesTitle());

        excelHelpers\WriteHelper::save($this->oExcel, $this->file);
    }

    public function beforeExecute()
    {
        $row = $this->getConfigVal('row');
        if ($row <= $this->skip_row) {
            $row = $this->skip_row + 1;
            $this->setConfigVal('row', $row);
        }

        $this->oExcel = excelHelpers\WriteHelper::loadWorkBookFromFile($this->file);
        $this->sheet = $this->oExcel->setActiveSheetIndex(0);
    }

    /**
     * Записывает строку данных в текущий активный лист
     *
     * @param int $iRowIndex - индекс строки
     * @param array $aBuffer массив данных
     */
    public function writeRow($iRowIndex, $aBuffer)
    {
        excelHelpers\WriteHelper::writeRow($this->oExcel, $aBuffer, $iRowIndex);

        // Если массив с данными содержит поле template, то делаем выпадающий список
        if (($iIndexTemplate = array_search('template', array_keys($aBuffer))) !== false) {
            $this->setDropDownOnCell($iIndexTemplate, $iRowIndex, 'templates');
        }

        // Если массив с данными содержит поле visible, то делаем выпадающий список
        if (($iIndexTemplate = array_search('visible', array_keys($aBuffer))) !== false) {
            $this->setDropDownOnCell($iIndexTemplate, $iRowIndex, 'visible');
        }
    }

    public function init()
    {
        $row = $this->getConfigVal('row');
        if (!$row) {
            $this->setConfigVal('row', $this->skip_row + 1);
        }
    }

    /** Метод вызываемый после очередной итерации на запись */
    public function afterExecute()
    {
        excelHelpers\WriteHelper::save($this->oExcel, $this->file);
    }

    /**
     * Установить выпадающий список на ячейку.
     *
     * @param int $iColumnIndex - индекс колонки
     * @param int $iRowIndex - индекс строки
     * @param string $sNameRange - имя области со значениями
     */
    public function setDropDownOnCell($iColumnIndex, $iRowIndex, $sNameRange)
    {
        $objValidation = $this->oExcel->getActiveSheet()->getCellByColumnAndRow($iColumnIndex, $iRowIndex)->getDataValidation();
        $objValidation->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST);
        $objValidation->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(false);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setErrorTitle('Input error');
        $objValidation->setError('Value is not in list.');
        $objValidation->setPromptTitle('Pick from list');
        $objValidation->setPrompt('Please pick a value from the drop-down list.');
        $objValidation->setFormula1("={$sNameRange}");
    }

    /**
     * Добавить именнованую область со списком значений.
     *
     * @param string $sColumnCoordinate - название колонки
     * @param string $sNameRange - название области
     * @param array $aData - список значений
     */
    public function addNamedRangeWithValuesList($sColumnCoordinate, $sNameRange, $aData)
    {
        // Переиндексируем массив
        $aData = array_values($aData);

        $iCount = count($aData);

        for ($i = 0; $i < $iCount; ++$i) {
            $this->oExcel->getActiveSheet()->SetCellValue($sColumnCoordinate . ($i + 1), $aData[$i]);
        }

        $this->oExcel->addNamedRange(
            new \PHPExcel_NamedRange(
                $sNameRange,
                $this->oExcel->getActiveSheet(),
                "{$sColumnCoordinate}1:{$sColumnCoordinate}{$iCount}"
            )
        );
    }
}
