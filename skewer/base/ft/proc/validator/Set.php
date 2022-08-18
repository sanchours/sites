<?php

namespace skewer\base\ft\proc\validator;

class Set extends Prototype
{
    /**
     * Проверяет данные на соответствие условиям
     *
     * @return bool
     */
    public function isValid()
    {
        return (bool) $this->getValue();
    }

    /**
     * Отдает текст ошибки.
     *
     * @return string
     */
    public function getErrorText()
    {
        return \Yii::t('ft', 'error_validator_set');
    }
}
