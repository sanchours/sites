<?php

namespace skewer\components\import\field;

/**
 * Обработчик поля типа значение.
 */
class Value extends Prototype
{
    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    public function getValue()
    {
        return implode(',', $this->values);
    }
}
