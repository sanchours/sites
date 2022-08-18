<?php

namespace skewer\base\ft\converter;

use skewer\base\ft;

/**
 * Класс для преобразования массивов ft описания в класс skewer\base\ft\Model и обратно.
 */
class Arr implements ConverterInterface
{
    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param array $aIn входные данные
     *
     * @return ft\Model
     */
    public function dataToFtModel($aIn)
    {
        return new ft\Model($aIn);
    }

    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param ft\Model $oModel модель данных для экспорта
     *
     * @return array
     */
    public function ftModelToData(ft\Model $oModel)
    {
        return $oModel->getModelArray();
    }
}
