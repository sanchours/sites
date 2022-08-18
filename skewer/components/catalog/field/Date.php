<?php

namespace skewer\components\catalog\field;

class Date extends Prototype
{
    protected function build($value, $rowId, $aParams)
    {
        $html = ($value) ? $this->getHtmlData(date('d.m.Y', strtotime($value))) : '';

        return [
            'value' => ($value == '0000-00-00') ? '' : $value,
            'tab' => $value ? date('d.m.Y', strtotime($value)) : '',
            'html' => $html,
        ];
    }

    public static function canBeNull()
    {
        return true;
    }

    public static function convertValueToNull($sValue)
    {
        $aConvertions = [
            '',
        ];

        if (array_search($sValue, $aConvertions) !== false) {
            return true;
        }

        return false;
    }
}
