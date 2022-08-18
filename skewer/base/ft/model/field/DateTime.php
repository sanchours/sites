<?php

namespace skewer\base\ft\model\field;

use skewer\base\ft;

/**
 * Класс поля типа datetime
 * Class DateTime.
 */
class DateTime extends ft\model\Field
{
    public function getDefault()
    {
        $sVal = parent::getDefault();

        if ($sVal === 'now') {
            $sVal = date('Y-m-d H:i:s');
        }

        return $sVal;
    }
}
