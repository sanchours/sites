<?php

namespace skewer\components\catalog\field;

class Datetime extends Prototype
{
    protected function build($value, $rowId, $aParams)
    {
        $html = ($value) ? $this->getHtmlData(date('d.m.Y H:i', strtotime($value))) : '';

        return [
            'value' => ((int) $value) ? $value : '',
            'tab' => $value ? date('d.m.Y H:i', strtotime($value)) : '',
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
