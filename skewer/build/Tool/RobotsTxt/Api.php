<?php

namespace skewer\build\Tool\RobotsTxt;

use skewer\base\SysVar;

class Api
{
    /**
     * @const string категория модуля Tool\RobotsTxt в таблице sysvar
     */
    const SYSVAR_CATEGORY = 'RobotsTxt';

    /**
     * Получить значения системной переменной.
     *
     * @param string $sParamName - название параметра
     * @param mixed $mDefaultValue - значение по умолчанию
     *
     * @return string
     */
    public static function getSysVar($sParamName, $mDefaultValue)
    {
        return SysVar::get(self::SYSVAR_CATEGORY . '.' . $sParamName, $mDefaultValue);
    }

    /**
     * Сохранение значения системной переменной.
     *
     * @param string $sParamName - название параметра
     * @param mixed $mValue значение переменной
     *
     * @return string
     */
    public static function setSysVar($sParamName, $mValue)
    {
        return SysVar::set(self::SYSVAR_CATEGORY . '.' . $sParamName, $mValue);
    }
}
