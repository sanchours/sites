<?php
/**
 * User: Max
 * Date: 31.07.14.
 */

namespace skewer\components\ext\field;

class TextCss extends Text
{
    /**
     * Отдает название типа отображения.
     *
     * @return string
     */
    public function getView()
    {
        return 'text_css';
    }
}
