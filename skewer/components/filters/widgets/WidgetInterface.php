<?php

namespace skewer\components\filters\widgets;

/**
 * Interface WidgetInterface.
 */
interface WidgetInterface
{
    /**
     * Вернёт тип виджета.
     *
     * @return string
     */
    public static function getTypeWidget();
}
