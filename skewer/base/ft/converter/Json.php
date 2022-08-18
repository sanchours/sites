<?php

namespace skewer\base\ft\converter;

use skewer\base\ft;

/**
 * Класс для преобразования json ft описания в класс skewer\base\ft\Model.
 */
class Json implements ConverterInterface
{
    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param string $sIn входные данные
     *
     * @return ft\Model
     */
    public function dataToFtModel($sIn)
    {
        return new ft\Model(json_decode($sIn, true));
    }

    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param ft\Model $oModel модель данных для экспорта
     *
     * @return string
     */
    public function ftModelToData(ft\Model $oModel)
    {
        return json_encode($oModel->getModelArray());
    }
}
