<?php

namespace skewer\components\seo;

use skewer\base\SysVar;

abstract class FileSEO
{
    public static $nameFile;

    protected static $nameParam;

    /**
     * Путь к файлу относительно папки web/.
     *
     * @return string
     */
    final public static function getDefaultValue()
    {
        return static::$nameFile;
    }

    final public static function getFullFilePath()
    {
        return WEBPATH . SysVar::get(static::$nameParam, static::getDefaultValue());
    }

    /**
     * Установка расположения к файлу относительно папки web/.
     *
     * @return string
     */
    final public static function setDefaultValue()
    {
        return self::setShortPathSysVar(static::getDefaultValue());
    }

    final public static function setShortPathSysVar($shortPath)
    {
        return SysVar::set(static::$nameParam, $shortPath);
    }

    final public static function getDirPath()
    {
        return str_replace(static::$nameFile, '', static::getFullFilePath());
    }
}
