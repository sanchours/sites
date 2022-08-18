<?php

namespace skewer\components\targets;

use skewer\base\SysVar;

/**
 * Библиотека для работы с Яндекс Целями
 * Class Yandex.
 */
class Yandex
{
    /** имя параметра для счетчика яндекса */
    const contName = 'yaReachGoalCounter';

    /**
     * Отдает флаг активности счетчика.
     *
     * @return bool
     */
    public static function isActive()
    {
        return (bool) self::getCounter();
    }

    /**
     * Отдает строку с номером счетчика.
     *
     * @return int
     */
    public static function getCounter()
    {
        return (int) SysVar::get(self::contName);
    }

    /**
     * Сохраняет счетчик.
     *
     * @param $iCounter
     */
    public static function setCounter($iCounter)
    {
        SysVar::set(self::contName, (int) $iCounter);
    }
}
