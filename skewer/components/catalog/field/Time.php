<?php

namespace skewer\components\catalog\field;

class Time extends Prototype
{
    protected function build($value, $rowId, $aParams)
    {
        $out = '';

        if ($value) {
            list($sH, $sM) = explode(':', $value);
            $out = $sH . ':' . $sM;
        }

        return [
            'value' => $value,
            'html' => $out,
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
