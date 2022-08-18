<?php

namespace skewer\build\Page\BlindVersion;

use skewer\base\site_module\Request;

class Api
{
    const BLIND_MODE = 'blind_mode';

    const SIMPLE_MODE = 'simple_mode';

    const COLOR_SCHEMA = 'color_schema';

    public static function isBlindVersion()
    {
        return isset($_SESSION[self::BLIND_MODE]) and $_SESSION[self::BLIND_MODE];
    }

    public static function onBlindVersion()
    {
        $_SESSION[self::BLIND_MODE] = true;
    }

    public static function offBlindVersion()
    {
        $_SESSION[self::BLIND_MODE] = false;
        unset($_SESSION['svSize'], $_SESSION['svSpace'], $_SESSION['svNoimg'], $_SESSION['svColor']);
    }

    public static function getBlindParam($sParam, $mDefValue = false)
    {
        $val = Request::getStr($sParam, '', $_SESSION[$sParam] ?? $mDefValue);
        $_SESSION[$sParam] = $val;

        return $val;
    }

    public static function getClass4BodyTag()
    {
        $aParams = [];

        if (Api::getBlindParam('svSize')) {
            $aParams[] = 'g-size';
            $aParams[] = 'g-size' . Api::getBlindParam('svSize');
        }

        if (Api::getBlindParam('svSpace')) {
            $aParams[] = 'g-space';
            $aParams[] = 'g-space' . Api::getBlindParam('svSpace');
        }

        if (Api::getBlindParam('svNoimg') == 2) {
            $aParams[] = 'g-noimg';
        }

        $aParams[] = 'g-main';

        return implode(' ', $aParams);
    }
}
