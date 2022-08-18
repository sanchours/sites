<?php

namespace skewer\components\modifications;

class Api
{
    /** событие по сбору активных поисковых движков */
    const EVENT_GET_MODIFICATION = 'event_get_modification';

    private static $iMaxTime = 0;

    public static function getMaxTime()
    {
        $oEvent = new \skewer\components\modifications\GetModificationEvent();

        \Yii::$app->trigger(self::EVENT_GET_MODIFICATION, $oEvent);
        self::$iMaxTime = $oEvent->getLastTime();

        return self::$iMaxTime;
    }
}
