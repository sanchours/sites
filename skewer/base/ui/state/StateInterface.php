<?php

namespace skewer\base\ui\state;

use skewer\base\ft;

/**
 * Определение обязательных методов состояния пользовательского интерфейса
 * который может отображать данные.
 */
interface StateInterface extends BaseInterface
{
    /**
     * Задает набор полей по FT модели.
     *
     * @param ft\Model $oModel описание модели
     * @param string $mColSet набор колонок для вывода
     */
    public function setFieldsByFtModel(ft\Model $oModel, $mColSet = '');
}
