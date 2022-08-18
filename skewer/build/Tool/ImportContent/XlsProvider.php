<?php

namespace skewer\build\Tool\ImportContent;

use skewer\components\import\provider\Xls;

require_once RELEASEPATH . 'libs/excel/Classes/PHPExcel.php';
require_once RELEASEPATH . 'libs/excel/Classes/ChunkReadFilter.php';
require_once RELEASEPATH . 'libs/excel/Classes/PHPExcel/Reader/Excel2007.php';
require_once RELEASEPATH . 'libs/excel/Classes/PHPExcel/Reader/Excel5.php';

/**
 * Провайдер для xsl
 * Class Xls.
 */
class XlsProvider extends Xls
{
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
            $aRes[$l] = $this->encode(trim($this->sheet->getCellByColumnAndRow($l, $i)->getValue()));
        }

        return $aRes;
    }
}
