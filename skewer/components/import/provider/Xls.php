<?php

namespace skewer\components\import\provider;

use skewer\components\excelHelpers;

require_once RELEASEPATH . 'libs/excel/Classes/PHPExcel.php';
require_once RELEASEPATH . 'libs/excel/Classes/ChunkReadFilter.php';
require_once RELEASEPATH . 'libs/excel/Classes/PHPExcel/Reader/Excel2007.php';
require_once RELEASEPATH . 'libs/excel/Classes/PHPExcel/Reader/Excel5.php';

/**
 * Провайдер для xsl
 * Class Xls.
 */
class Xls extends Prototype
{
    /** @var int Максимальное кол-во строк, читаемое за 1 раз */
    const Limit = 200;

    /** @var bool|\PHPExcel_Worksheet */
    protected $sheet = false;

    /** @var int Кол-во пустых строк, означающих конец файла */
    private $empty = 5;

    /** @var int Счетчик прочитанных строк */
    private $count = 0;

    /** @var int Кол-во читаемый столбцов */
    protected $row_count = 15;

    /** @var int Пропускать строки */
    protected $skip_row = 0;

    protected $parameters = [
        'row_count' => [
            'title' => 'field_xls_row_count',
            'datatype' => 'i',
            'viewtype' => 'int',
            'default' => '15',
            'params' => [
                'minValue' => 0,
                'allowDecimals' => false,
            ],
        ],
        'skip_row' => [
            'title' => 'field_skip_row',
            'datatype' => 'i',
            'viewtype' => 'int',
            'default' => '0',
            'params' => [
                'minValue' => 0,
                'allowDecimals' => false,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getAllowedExtension()
    {
        return ['xls', 'xlsx'];
    }

    public function init()
    {
        $row = $this->getConfigVal('row');
        if (!$row) {
            $this->setConfigVal('row', 1);
        }
    }

    /**
     * Метод, вызываемый перед началом итеративного чтения.
     *
     * @return mixed
     */
    public function beforeExecute()
    {
        $row = $this->getConfigVal('row');
        if ($row <= $this->skip_row) {
            $row = $this->skip_row + 1;
            $this->setConfigVal('row', $row);
        }
        $this->openFile($row, self::Limit);
    }

    /**
     * Метод, вызываемый после прохождения итераций.
     *
     * @return mixed
     */
    public function afterExecute()
    {
    }

    /**
     * Проверка массива на пустые значения. Возвращает true, если в массиве все значения пусты.
     *
     * @param array $aRow
     *
     * @return bool
     */
    private function isEmpty($aRow = [])
    {
        $aVal = array_count_values($aRow);

        return  empty($aRow) || (isset($aVal['']) && $aVal[''] == count($aRow));
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRow()
    {
        if ($this->empty == 0) {
            $this->empty = 5;
        }

        //строка для начала чтения
        $row = $this->getConfigVal('row');

        //читаем строку
        $buffer = $this->readRow($row);

        $empty = $this->isEmpty($buffer);

        ++$row;

        //запомним строку
        $this->setConfigVal('row', $row);

        if ($empty) {
            --$this->empty;
        } else {
            $this->empty = 5;
        }

        if (!$this->empty) {
            return false;
        }

        ++$this->count;

        //Прочитали буфер - пора заканчивать
        if ($this->count == self::Limit) {
            $this->canRead = false;
        }

        if ($empty) {
            return $this->getRow();
        }

        return $buffer;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getExample()
    {
        $this->openFile($this->skip_row + 1);
        $aRes = [];
        for ($i = $this->skip_row + 1; $i <= $this->skip_row + 5; ++$i) {
            $aRow = $this->readRow($i);
            if ($this->isEmpty($aRow)) {
                continue;
            }
            $aRes[] = implode(' ', $aRow);
        }

        return implode('</br>', $aRes);
    }

    /**
     * Читаем $i-ю строку.
     *
     * @param $i
     *
     * @return array
     */
    protected function readRow($i)
    {
        $aRes = [];
        for ($l = 0; $l < $this->row_count; ++$l) {
            $aRes[$l] = $this->encode(trim(htmlspecialchars($this->sheet->getCellByColumnAndRow($l, $i)->getValue())));
        }

        return $aRes;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getInfoRow()
    {
        $this->openFile($this->skip_row + 1);
        $aRow = $this->readRow($this->skip_row + 1);

        return $this->isEmpty($aRow) ? [] : $aRow;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPureString()
    {
        $this->openFile();
        $sRes = '';
        for ($l = 0; $l < $this->row_count; ++$l) {
            $sRes .= trim(htmlspecialchars($this->sheet->getCellByColumnAndRow($l, $this->skip_row + 1)->getValue()));
        }

        return $sRes;
    }

    /**
     * Открытие файла.
     *
     * @param int $startRow
     * @param int $limit
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     *
     * @return bool
     */
    private function openFile($startRow = 1, $limit = 5)
    {
        if (!$this->file) {
            return false;
        }
        if (!file_exists($this->file)) {
            return false;
        }

        $chunkFilter = new \ChunkReadFilter();
        $chunkFilter->setRows($startRow, $limit); 	//устанавливаем значение фильтра

        $objPHPExcel = excelHelpers\ReadHelper::createReaderForFile($this->file, $chunkFilter);
        $objPHPExcel->setActiveSheetIndex(0);		//устанавливаем индекс активной страницы
        $this->sheet = $objPHPExcel->getActiveSheet();	//делаем активной нужную страницу

        return true;
    }
}
