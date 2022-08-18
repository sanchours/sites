<?php

namespace skewer\base\ui\state;

use skewer\base\ft;

/**
 * Интерфейс для построения формы отображения данных
 * Class ListInterface.
 */
interface ShowInterface extends StateInterface
{
    /**
     * Устанавливает значения для вывода.
     *
     * @param array|ft\ArPrototype $aValues набор пар имя поля - значение
     */
    public function setValues($aValues);
}
