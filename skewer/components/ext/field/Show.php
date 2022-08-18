<?php

namespace skewer\components\ext\field;

/**
 * Редактор "показать" - простое отображение без возможности редактирования.
 */
class Show extends Prototype
{
    /**
     * Отдает название типа отображения.
     *
     * @return string
     */
    public function getView()
    {
        return 'show';
    }
}
