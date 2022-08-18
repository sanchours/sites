<?php

namespace skewer\base\ft\converter;

use skewer\base\ft;

/**
 * Интерфейс для классов преобразования в ft модель и из нее.
 */
interface ConverterInterface
{
    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param string $sIn входные данные
     *
     * @return ft\Model
     */
    public function dataToFtModel($sIn);

    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param ft\Model $oModel модель данных для экспорта
     *
     * @return string
     */
    public function ftModelToData(ft\Model $oModel);
}
