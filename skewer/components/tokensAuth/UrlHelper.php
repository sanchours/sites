<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:49.
 */

namespace skewer\components\tokensAuth;

class UrlHelper
{
    public static function getTokensUrl($sCmd)
    {
        return Config::$sTokensUrl . $sCmd;
    }
}
