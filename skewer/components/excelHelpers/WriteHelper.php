<?php

namespace skewer\components\excelHelpers;

use skewer\helpers\Files;

require_once RELEASEPATH . 'libs/excel/Classes/PHPExcel.php';

/**
 * Class WriteHelper.
 */
class WriteHelper
{
    /**
     * Отдаёт файл на скачивание в браузер
     *
     * @param \PHPExcel $oExcel
     * @param $sFileName - имя файла( Пример: 'тест.xls' )
     * @param string $sWriterType - тип файла('Excel2007' или 'Excel5')
     */
    public static function downloadFileInBrowser(\PHPExcel $oExcel, $sFileName, $sWriterType = 'Excel2007')
    {
        $sFileName = self::buildFileNameWithExtension($sFileName, $sWriterType);

        if ($sWriterType == 'Excel2007') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } else {
            header('Content-Type: application/vnd.ms-excel');
        }

        header('Content-Disposition: attachment;filename="' . $sFileName . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($oExcel, $sWriterType);
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Создает excel объект из файла.
     *
     * @param $sFilePath - путь к фалу
     *
     * @return \PHPExcel
     */
    public static function loadWorkBookFromFile($sFilePath)
    {
        return \PHPExcel_IOFactory::load($sFilePath);
    }

    /**
     * Создаёт новый excel объект
     *
     * @return \PHPExcel
     */
    public static function createNewWorkBook()
    {
        return new \PHPExcel();
    }

    /**
     * Записывает строку данных в текущий активный лист
     *
     * @param \PHPExcel $oExcel - excel объект
     * @param array $aBuffer массив данных
     *      Формат значений:
     *      1. Значение + стиль ячейки
     *           [
     *               ['value' => '123', 'style' => Styles::$RED],
     *               ['value' => '456', 'style' => [Styles::$RED, Styles::$HEADER]],
     *           ]
     *
     *      2. Значение
     *          [ '123', '234', '345' ]
     * @param int $iRowIndex - индекс строки
     * @param int $iColumnIndex - индекс столбца
     */
    public static function writeRow(\PHPExcel $oExcel, $aBuffer, $iRowIndex = 1, $iColumnIndex = 0)
    {
        foreach ($aBuffer as $value) {
            if (is_array($value)) {
                $oExcel->getActiveSheet()
                    ->setCellValueByColumnAndRow($iColumnIndex, $iRowIndex, $value['value']);

                if (!empty($value['style'])) {
                    $bMultipleStyles = false;
                    foreach (array_keys($value['style']) as $styleKey) {
                        if (is_int($styleKey)) {
                            $bMultipleStyles = true;
                        }

                        break;
                    }

                    if ($bMultipleStyles) {
                        foreach ($value['style'] as $aStyle) {
                            self::setStyleCell($oExcel, $aStyle, $iColumnIndex, $iRowIndex);
                        }
                    } else {
                        self::setStyleCell($oExcel, $value['style'], $iColumnIndex, $iRowIndex);
                    }
                }

                if (!empty($value['width'])) {
                    self::setWidthColumn($oExcel, $value['width'], $iColumnIndex);
                }
            } else {
                $oExcel->getActiveSheet()
                    ->setCellValueByColumnAndRow($iColumnIndex, $iRowIndex, $value);
            }

            ++$iColumnIndex;
        }
    }

    /**
     * Устанавливает ширину колонок.
     *
     * @param \PHPExcel $oExcel
     * @param $iValue - значения для всех колонок
     * @param array|int|string $mColumnIndex
     *      Формат значений:
     *      1. 2 - Эквивалентно колонке C
     *      2. "A"
     *      3. [0, 2, "D", "G"] - Можно задавать в одном массиве как индексами, так и символами
     */
    public static function setWidthColumn(\PHPExcel $oExcel, $iValue, $mColumnIndex)
    {
        if (is_int($mColumnIndex)) {
            $oExcel->getActiveSheet()
                ->getColumnDimensionByColumn($mColumnIndex)
                ->setWidth($iValue);
        } elseif (is_array($mColumnIndex)) {
            foreach ($mColumnIndex as $columnIndex) {
                if (is_int($columnIndex)) {
                    $oExcel->getActiveSheet()
                        ->getColumnDimensionByColumn($columnIndex)
                        ->setWidth($iValue);
                } else {
                    $oExcel->getActiveSheet()
                        ->getColumnDimension($columnIndex)
                        ->setWidth($iValue);
                }
            }
        } else {
            $oExcel->getActiveSheet()
                ->getColumnDimension($mColumnIndex)
                ->setWidth($iValue);
        }
    }

    /**
     * Запись строки в ячейку.
     *
     * @param \PHPExcel $oExcel
     * @param string $sValue - Значение в строковом представлении
     * @param int $iRowIndex
     * @param int $iColumnIndex
     * @param array $aStyles массив значений стилей
     *      Формат значений:
     *      1. Одиночные стили
     *          array (
     *              'fill' => array(
     *                  'type' => \PHPExcel_Style_Fill::FILL_SOLID,
     *                  'color' => array(
     *                       'rgb' => '98FB98'
     *                   )
     *              )
     *          )
     *   2. Групповые стили, удобно для дополнения спец стилей на основе шаблона стилей. Применяются в порядке очереди
     *          0 => array (
     *              'fill' => array(
     *                  'type' => \PHPExcel_Style_Fill::FILL_SOLID,
     *                  'color' => array(
     *                       'rgb' => '98FB98'
     *                   )
     *              )
     *          )
     *          1 => array (
     *              'alignment' => array(
     *                   'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
     *               ),
     *               'font' => array(
     *                   'bold' => true
     *               )
     *          )
     * )
     */
    public static function writeCell(\PHPExcel $oExcel, $sValue, $iRowIndex = 1, $iColumnIndex = 0, $aStyles = [])
    {
        $oExcel->getActiveSheet()
            ->setCellValueByColumnAndRow($iColumnIndex, $iRowIndex, $sValue);

        if (count($aStyles)) {
            self::setStyleCell($oExcel, $aStyles, $iColumnIndex, $iRowIndex);
        }
    }

    /**
     * Применение стилей для ячейки.
     *
     * @param \PHPExcel $oExcel
     * @param array $aStyles
     * @param int $iColumnIndex
     * @param int $iRowIndex
     */
    public static function setStyleCell(\PHPExcel $oExcel, $aStyles, $iColumnIndex, $iRowIndex)
    {
        $bMultipleStyles = false;
        foreach (array_keys($aStyles) as $value) {
            if (is_int($value)) {
                $bMultipleStyles = true;
            }

            break;
        }

        if ($bMultipleStyles) {
            foreach ($aStyles as $aStyle) {
                $oExcel->getActiveSheet()
                    ->getStyleByColumnAndRow($iColumnIndex, $iRowIndex)
                    ->applyFromArray($aStyle);
            }
        } else {
            $oExcel->getActiveSheet()
                ->getStyleByColumnAndRow($iColumnIndex, $iRowIndex)
                ->applyFromArray($aStyles);
        }
    }

    /**
     * Устанавливает стили для области.
     *
     * @param \PHPExcel $oExcel
     * @param array $aStyles
     * @param array|string $mRanges
     *      Формат значений:
     *      1. string "A1:B1"
     *      2. string "A1"
     *      3. array ["A1", "B2", "C3"]
     *      4. array ["A2:D3", "B3:C3"]
     */
    public static function setStyleRange(\PHPExcel $oExcel, $aStyles, $mRanges)
    {
        $bMultipleStyles = false;
        foreach (array_keys($aStyles) as $value) {
            if (is_int($value)) {
                $bMultipleStyles = true;
            }

            break;
        }

        if (!is_array($mRanges)) {
            $aRanges[] = $mRanges;
        } else {
            $aRanges = $mRanges;
        }

        foreach ($aRanges as $sRange) {
            if ($bMultipleStyles) {
                foreach ($aStyles as $aStyle) {
                    $oExcel->getActiveSheet()
                        ->getStyle($sRange)
                        ->applyFromArray($aStyle);
                }
            } else {
                $oExcel->getActiveSheet()
                    ->getStyle($sRange)
                    ->applyFromArray($aStyles);
            }
        }
    }

    /**
     * Сохранение в файл.
     *
     * @param \PHPExcel $oExcel - excel объект
     * @param string $sFilePath - путь к фалу
     * @param string $sWriterType - тип файла ('Excel2007' или 'Excel5')
     * @param bool $bPreCalculateFormulas - пересчитывать формулы перед сохранением? !!! Длительная операция
     * @param bool $bOverWrite - флаг перезаписи файла(true - перезапишет существующий файл, false - создаст новый с уникальным именем)
     */
    public static function save(\PHPExcel $oExcel, $sFilePath, $sWriterType = 'Excel2007', $bPreCalculateFormulas = false, $bOverWrite = true)
    {
        /** @var \PHPExcel_Writer_Abstract $objWriter */
        $objWriter = \PHPExcel_IOFactory::createWriter($oExcel, $sWriterType);
        $objWriter->setPreCalculateFormulas($bPreCalculateFormulas);
        $objWriter->save(self::buildFileNameWithExtension($sFilePath, $sWriterType, !$bOverWrite));
    }

    /**
     * Устанавливает расширение файла $sFileName в зависимости от типа файла $sWriterType.
     *
     * @param string $sFileName - имя файла
     * @param string $sWriterType - тип файла ('Excel2007' или 'Excel5')
     * @param bool $bGenerateUniqName
     *
     * @return string
     */
    private static function buildFileNameWithExtension($sFileName, $sWriterType, $bGenerateUniqName = false)
    {
        $aWriterToExtension = [
            'Excel2007' => '.xlsx',
            'Excel5' => '.xls',
        ];

        $sEncodingFileName = 'utf-8';
        if ($iPosDot = mb_strrpos($sFileName, '.', $sEncodingFileName)) {
            $sFileName = mb_substr($sFileName, 0, $iPosDot, $sEncodingFileName);
        }

        if (isset($aWriterToExtension[$sWriterType])) {
            $sFileName .= $aWriterToExtension[$sWriterType];
        }

        if ($bGenerateUniqName) {
            $sBaseName = basename($sFileName);
            $sDirPath = str_replace($sBaseName, '', $sFileName);
            $sFileName = Files::generateUniqFileName($sDirPath, $sBaseName);
        }

        return $sFileName;
    }

    /**
     * Сделать ячейку гиперссылкой.
     *
     * @param \PHPExcel $oExcel - excel-объект
     * @param $sCellCoordinate - координаты ячейки('D13')
     * @param $sUrl - ссылка на web-ресурс.
     * Для создания гиперссылки на другой лист или ячейку используйте формат "sheet://'Sheetname'!A1"
     */
    public static function changeCellClickableURL(\PHPExcel $oExcel, $sCellCoordinate, $sUrl)
    {
        $oExcel->getActiveSheet()->getCell($sCellCoordinate)->getHyperlink()->setUrl($sUrl);
    }

    /**
     * Назначение имени рабочему листу.
     *
     * @param \PHPExcel $oExcel
     * @param $sNameSheet
     */
    public static function setTitleActiveSheet(\PHPExcel $oExcel, $sNameSheet)
    {
        $oExcel->getActiveSheet()->setTitle($sNameSheet);
    }

    /**
     * Установить авторазмер для конкретных колонок.
     *
     * @param \PHPExcel $oExcel
     * @param array|string $mColumn
     *      Формат значений:
     *      1. 'D'
     *      2. ['A', 'B', 'D']
     */
    public static function setAutoSizeColumn(\PHPExcel $oExcel, $mColumn)
    {
        if (is_array($mColumn)) {
            foreach ($mColumn as $sColumn) {
                $oExcel->getActiveSheet()
                    ->getColumnDimension($sColumn)
                    ->setAutoSize(true);
            }
        } else {
            $oExcel->getActiveSheet()
                ->getColumnDimension($mColumn)
                ->setAutoSize(true);
        }
    }

    /**
     * Устанавливает авторазмер для колонок вместе с промежуточными столбцами между ними
     * основываясь на двух крайних значений.
     * !Порядорк указания неважен.
     * !Функция работает только для простых односимвольных букв английского алфавита.
     *
     * @param \PHPExcel $oExcel
     * @param array $aColumn
     *   Формат значений:
     *   1. ['A', 'D']
     *   Результат: Для столбцов A,B,C,D будет установлен авторазмер колонок
     */
    public static function setAutoSizeColumnRange(\PHPExcel $oExcel, $aColumn)
    {
        foreach (range($aColumn[0], $aColumn[1]) as $sColumn) {
            $oExcel->getActiveSheet()
                ->getColumnDimension($sColumn)
                ->setAutoSize(true);
        }
    }
}
