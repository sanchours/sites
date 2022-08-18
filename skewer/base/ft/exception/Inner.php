<?php

namespace skewer\base\ft\exception;

use skewer\base\ft as ft;

/**
 * Исключение системного ft уровня
 * Если возникли проблемы в выполненнии внутри ft.
 */
class Inner extends ft\Exception
{
    /**
     * Отдает набор путей для исключения из трассировки.
     *
     * @return array
     */
    protected function getSkipList()
    {
        return [];
    }
}
