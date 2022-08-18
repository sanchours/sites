<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 11.05.2016
 * Time: 16:59.
 */

namespace skewer\build\Adm\GuestBook;

use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\components\modifications\GetModificationEvent;

class Api
{
    public static function getLastModification()
    {
        $aRow = GuestBook::find()
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->one();

        if (isset($aRow['date_time'])) {
            return strtotime($aRow['date_time']);
        }

        return 0;
    }

    public static function className()
    {
        return 'skewer\build\Adm\GuestBook\Api';
    }

    public static function getLastMod(GetModificationEvent $event)
    {
        $event->setLastTime(Api::getLastModification());
    }
}
