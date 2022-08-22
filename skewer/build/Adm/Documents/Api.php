<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 11.05.2016
 * Time: 16:59.
 */

namespace skewer\build\Adm\Documents;

use skewer\build\Adm\Documents\models\Documents;
use skewer\components\modifications\GetModificationEvent;

class Api
{
    public static function getLastModification()
    {
        $aRow = Documents::find()
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
        return 'skewer\build\Adm\Documents\Api';
    }

    public static function getLastMod(GetModificationEvent $event)
    {
        $event->setLastTime(Api::getLastModification());
    }
}
