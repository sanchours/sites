<?php

namespace skewer\components\forms\components\protection;

class BlockJs implements IProtection
{
    public static $nameField = 'useJs';

    public static function getHtml()
    {
    }

    public static function regJs()
    {
        AssetBlockJs::register(\Yii::$app->view);
    }

    public static function check($aData = [])
    {
        if (isset($aData[self::$nameField]) && $aData[self::$nameField]) {
            return false;
        }

        return true;
    }
}
