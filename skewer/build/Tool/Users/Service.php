<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 10.05.2017
 * Time: 12:30.
 */

namespace skewer\build\Tool\Users;

use skewer\base\log\models\Log;
use skewer\base\orm;
use skewer\base\site\ServicePrototype;

class Service extends ServicePrototype
{
    /**
     * Удаляет устаревшие логи старше N дней.
     *
     * @return bool
     */
    public static function removeOldLogs()
    {
        /*Удаление всех записей кроме пользовательских*/
        $iDelayTime = cleanLog * 24 * 60 * 60;

        $iTime = time() - $iDelayTime;

        $sTime = date('Y-m-d H:i:s', $iTime);

        Log::deleteAll([
            'AND',
            'module != "Users"',
            ['<', 'event_time', $sTime],
        ]);

        /*Удаление пользовательских*/
        $iDelayTime = cleanUserLog * 24 * 60 * 60;

        $iTime = time() - $iDelayTime;

        $sTime = date('Y-m-d H:i:s', $iTime);

        Log::deleteAll([
            'AND',
            'module = "Users"',
            ['<', 'event_time', $sTime],
        ]);

        /* Удаление по лимиту */
        Log::deleteExpectLimit(logLimit);

        // оптимизация таблиц
        orm\DB::optimizeTables();

        return true;
    }
}
