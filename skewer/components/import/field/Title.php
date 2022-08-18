<?php

namespace skewer\components\import\field;

/**
 * Обработчик названия.
 */
class Title extends Prototype
{
    /**
     * Значение.
     *
     * @var string
     */
    private $value = '';

    public function beforeExecute()
    {
        $sVal = implode(',', $this->values);
        if (!$sVal) {
            $this->skipCurrentRow(true);
        }

        $this->value = $sVal;

        /* Запомним текущий товар для записей в логи */
        $this->config->setParam('current_title', $this->value);
    }

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
