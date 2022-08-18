<?php

namespace skewer\components\ext\field;

/**
 * Редактор "Упрощенный html редактор".
 */
class Html extends Text
{
    /**
     * Отдает название типа отображения.
     *
     * @return string
     */
    public function getView()
    {
        return 'html';
    }
}
