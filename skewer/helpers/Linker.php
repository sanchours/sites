<?php

namespace skewer\helpers;

use skewer\components\design\Design;

/**
 * Агрегатор client side файлов (css/js)
 * Сейчас используется только для динамической подгрузки в админском интерфейсе
 * В клиентской зачить полностью заменен на assets.
 */
class Linker
{
    /**
     * Список подключенных JS файлов.
     *
     * @var array
     */
    private static $aJSFiles = [];

    /**
     * Список подключенных CSS файлов.
     *
     * @var array
     */
    private static $aCSSFiles = [];

    /**
     * Добавляет в Linker JS-Файл $mFilePath.
     *
     * @static
     *
     * @param string $mFilePath Путь до файла
     *
     * @return bool
     */
    public static function addJsFile($mFilePath)
    {
        if (!$mFilePath) {
            return false;
        }

        self::$aJSFiles[$mFilePath] = $mFilePath;

        return true;
    }

    // func

    /**
     * Добавляет в Linker CSS-Файл $mFilePath.
     *
     * @static
     *
     * @param array|string $mFilePath Путь
     *
     * @return bool
     */
    public static function addCssFile($mFilePath)
    {
        if (!$mFilePath) {
            return false;
        }

        self::$aCSSFiles[$mFilePath] = $mFilePath;

        return true;
    }

    // func

    /**
     * Возвращает список собранных классом Linker JS файлов.
     *
     * @static
     *
     * @param bool $bValVersion подстановка версии
     *
     * @return array
     */
    public static function getJsFiles($bValVersion = true)
    {
        $lastUpdatedTime = Design::getLastUpdatedTime();

        if (!$bValVersion || !$lastUpdatedTime) {
            $aOut = self::$aJSFiles;
        } else {
            $aOut = array_map(
                static function ($path) use ($lastUpdatedTime) {
                    return $path . '?v=' . $lastUpdatedTime;
                },
                self::$aJSFiles
            );
        }

        return array_values($aOut);
    }

    /**
     * Возвращает список собранных классом Linker CSS файлов.
     *
     * @static
     *
     * @param bool $bValVersion подстановка версии
     *
     * @return array
     */
    public static function getCssFiles($bValVersion = true)
    {
        $lastUpdatedTime = Design::getLastUpdatedTime();

        if (!$bValVersion || !$lastUpdatedTime) {
            $aOut = self::$aCSSFiles;
        } else {
            $aOut = array_map(
                static function ($path) use ($lastUpdatedTime) {
                    return $path . '?v=' . $lastUpdatedTime;
                },
                self::$aCSSFiles
            );
        }

        return array_values($aOut);
    }

    /**
     * Очищает набор установленных css файлов.
     *
     * @static
     */
    public static function clearCssFiles()
    {
        self::$aCSSFiles = [];
    }

    /**
     * Очищает набор установленных js файлов.
     *
     * @static
     */
    public static function clearJsFiles()
    {
        self::$aJSFiles = [];
    }
}
