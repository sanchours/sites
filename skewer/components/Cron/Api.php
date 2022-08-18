<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 12.09.2016
 * Time: 8:43.
 */

namespace skewer\components\Cron;

use skewer\base\SysVar;

class Api
{
    public static $aConformity = [
        'c_min' => 0,
        'c_hour' => 1,
        'c_day' => 2,
        'c_month' => 3,
        'c_dow' => 4,
    ];

    public static $aFactors = [
        'c_min' => 60,
        'c_hour' => 3600,
        'c_day' => 86400,
        'c_month' => 2592000,
    ];

    private static function runDefault($aCurTask, $aCurTime)
    {
        /*Стандартный механизм. Запуск в конкретное время*/

        foreach ($aCurTask as $key => &$param) {
            $matches = [];
            preg_match('/[0-9]{1,2}-[0-9]{1,2}/', $param, $matches, PREG_OFFSET_CAPTURE);

            /*Если задан интервал, заигнорим его*/
            if (!empty($matches)) {
                $param = '*';
            }
        }

        if ($aCurTask['status'] &&
            ($aCurTask['c_min'] == '*' || $aCurTask['c_min'] == (int) $aCurTime[0]) &&
            ($aCurTask['c_hour'] == '*' || $aCurTask['c_hour'] == (int) $aCurTime[1]) &&
            ($aCurTask['c_day'] == '*' || $aCurTask['c_day'] == (int) $aCurTime[2]) &&
            ($aCurTask['c_month'] == '*' || $aCurTask['c_month'] == (int) $aCurTime[3]) &&
            ($aCurTask['c_dow'] == '*' || $aCurTask['c_dow'] == (int) $aCurTime[4])) {
            return true;
        }

        return false;
    }

    public static function runTask($aCurTask, $aCurTime)
    {
        /*Интервал запуска*/
        $bUseInterval = false;
        $bIntervalOk = true;
        foreach ($aCurTask as $key => $param) {
            $matches = [];
            preg_match('/[0-9]{1,2}-[0-9]{1,2}/', $param, $matches, PREG_OFFSET_CAPTURE);

            if (!empty($matches)) {
                list($iStart, $iEnd) = explode('-', $param);

                if (($aCurTime[self::$aConformity[$key]] > $iStart) and ($aCurTime[self::$aConformity[$key]] < $iEnd)) {
                } else {
                    /*Установим что по этому интервалу не пролезаем*/

                    $bIntervalOk = false;
                }

                /*Галка что интервал используется*/
                $bUseInterval = true;
            }
        }

        /*Запуск каждые*/
        /*Определим используется ли*/
        $aEveryTime = [];
        foreach ($aCurTask as $key => $param) {
            $matches = [];
            preg_match('/\\*\\/[0-9]{1,5}/', $param, $matches, PREG_OFFSET_CAPTURE);

            if (!empty($matches)) {
                $aEveryTime[$key] = str_replace('*/', '', $param);
            }
        }

        if ($bUseInterval) {
            if ($bIntervalOk) {
                /*Интервал используется и мы в него пролезаем*/
                if (!empty($aEveryTime)) {
                    /*"каждые" установлен*/
                    return self::runEvery($aCurTask, $aCurTime, $aEveryTime);
                }
                /*"Каждые не установлен"*/
                /*отработка стандартной механики с игнорированием интервала*/
                return self::runDefault($aCurTask, $aCurTime);
            }
            /*Интервал используется и в него не пролезаем*/
            return false;
        }
        if (!empty($aEveryTime)) {
            /*Интервал не используется. Но "каждые" установлен*/
            return self::runEvery($aCurTask, $aCurTime, $aEveryTime);
        }
        /*Интервал не используется! "Каждые" не используется*/
        /*Попробуем по стандартному механизму*/
        return self::runDefault($aCurTask, $aCurTime);

        return false;
    }

    private static function runEvery($aCurTask, $aCurTime, $aEveryTime)
    {
        foreach ($aCurTask as $key => &$param) {
            $matches = [];
            preg_match('/[0-9]{1,2}-[0-9]{1,2}/', $param, $matches, PREG_OFFSET_CAPTURE);

            /*Если задан интервал, заигнорим его. он уже был обработан ранее скорее всего*/
            if (!empty($matches)) {
                $param = '*';
            }
        }

        $sRunTimes = SysVar::get('ScheduleLastStartTimeTasks');

        $aRunTasks = [];

        if (($sRunTimes !== null) and ($sRunTimes !== '')) {
            $aRunTasks = json_decode($sRunTimes, true);
        }

        if (isset($aRunTasks[$aCurTask['id']])) {
            /*Пересчитаем размер сдвига в секундах*/
            $iDelay = 0;

            foreach ($aEveryTime as $key => $item) {
                $iDelay = $iDelay + $item * self::$aFactors[$key];
            }

            $sOperationTime = self::timestamp(strtotime(date('Y') . '-' . $aCurTime['3'] . '-' . $aCurTime['2'] . ' ' . $aCurTime['1'] . ':' . $aCurTime['0']));

            /*Время пришло?*/
            if ($aRunTasks[$aCurTask['id']] < $sOperationTime - $iDelay) {
                $aRunTasks[$aCurTask['id']] = self::timestamp(time());
                SysVar::set('ScheduleLastStartTimeTasks', json_encode($aRunTasks));

                return true;
            }

            return false;
        }
        /*Это первый запуск этой задачи*/
        /*Выполним немедленно!*/
        $aRunTasks[$aCurTask['id']] = self::timestamp(time());
        SysVar::set('ScheduleLastStartTimeTasks', json_encode($aRunTasks));

        return true;

        return false;
    }

    public static function timestamp($iTimeStamp)
    {
        $sTimezone = date('O');

        $sTimezone = str_replace('0', '', $sTimezone);

        if (mb_strpos($sTimezone, '+') !== false) {
            return $iTimeStamp + (int) $sTimezone * 3600;
        }

        return $iTimeStamp - (int) $sTimezone * 3600;

        return $iTimeStamp;
    }
}
