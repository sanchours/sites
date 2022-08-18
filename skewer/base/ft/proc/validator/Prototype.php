<?php

namespace skewer\base\ft\proc\validator;

use skewer\base\ft;

/**
 * Прототип валидатора.
 */
abstract class Prototype extends ft\proc\Prototype
{
    /**
     * Проверяет данные на соответствие условиям
     *
     * @return bool
     */
    abstract public function isValid();

    /**
     * Отдает текст ошибки.
     *
     * @return string
     */
    abstract public function getErrorText();
}
