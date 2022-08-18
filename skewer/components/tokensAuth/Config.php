<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:45.
 */

namespace skewer\components\tokensAuth;

class Config
{
    public static $sServiceName = 'tokens';

    public static $sVersion = '0.7';

    public static $sTokensUrl = null;

    public static function init()
    {
        self::$sTokensUrl = \Yii::$app->params['token_url'];
    }
}
