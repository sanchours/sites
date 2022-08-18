<?php

namespace skewer\base\ft\proc\validator;

class SystemName extends Prototype
{
    /**
     * Проверяет данные на соответствие условиям
     *
     * @return bool
     */
    public function isValid()
    {
        return preg_match('/^[a-z0-9_]*$/i', $this->getValue());
    }

    /**
     * Отдает текст ошибки.
     *
     * @return string
     */
    public function getErrorText()
    {
        return \Yii::t('ft', 'error_validator_sys_name');
    }
}
