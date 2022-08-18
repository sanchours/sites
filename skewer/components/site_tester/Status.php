<?php

namespace skewer\components\site_tester;

class Status
{
    const OK = 'ok';
    const WARNING = 'warning';
    const FAIL = 'fail';
    const ERROR = 'error';
    const SKIP = 'skip';
    const UNDEFINED = 'undefined';

    const DEFAULT_COLOR = '';

    public static $colors = [
         'undefined' => '#656D78',
         'ok' => '#2C9235',
         'warning' => '#ec971f',
         'fail' => '#d9534f',
         'error' => '#E9573F',
         'skip' => '#31b0d5',
         'message' => '#967ADC',
     ];

    public static function getColor($status)
    {
        return (isset(self::$colors[$status])) ? self::$colors[$status] : self::DEFAULT_COLOR;
    }
}
