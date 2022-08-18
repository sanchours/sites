<?php

namespace skewer\components\import\field;

/**
 * Обработчик поля типа массив значений.
 */
class ArrayValue extends Prototype
{
    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    public function getValue()
    {
        $result = '';
        if (is_array($this->values)) {
            foreach ($this->values as $value) {
                $values = explode('&@&', $value);
                foreach ($values as $item) {
                    $result .= $item . '<br/>';
                }
            }
        }

        return $result;
    }
}
