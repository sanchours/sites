<?php

namespace skewer\base\ui\state;

use skewer\base\ft;
use skewer\base\ui;

/**
 * Интерфейс для построения списка записей
 * Class ListInterface.
 */
interface ListInterface extends StateInterface
{
    /**
     * Задает набор данных для отображения.
     *
     * @param array[]|ft\ArPrototype[] $aValueList массив наборов данных
     */
    public function setValues($aValueList);

    /**
     * Устанавливает общее число записей на страницу.
     *
     * @param int $iValue значение
     */
    public function setOnPage($iValue);

    /**
     * Устанавливает общее число записей в хранилище.
     *
     * @param int $iValue значение
     */
    public function setTotal($iValue);

    /**
     * Устанавливает номер страницы
     * Счет начинается с 0.
     *
     * @param int $iValue значение
     */
    public function setPageNum($iValue);

    /**
     * Добавляет кнопку к строке.
     *
     * @param ui\element\RowButton $oButton описание кнопки
     */
    public function addRowBtn($oButton);
}
