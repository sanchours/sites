<?php

namespace skewer\base;

/**
 * Загрузка ресурсов по имени класса.
 */
class Autoloader
{
    /**
     * Закрываем возможность вызова напряумю.
     */
    private function __construct()
    {
    }

    /**
     * Регистрация загрузчика согласно пути.
     *
     * @static
     *
     * @return bool
     */
    public static function init()
    {
        spl_autoload_register([new self(), 'autoload']);

        return true;
    }

    // func

    /**
     * Разбор имени файла, подгрузка файла класса.
     *
     * @static
     *
     * @param string $sClassName Имя класса
     *
     * @return bool
     */
    public static function autoload($sClassName)
    {
        if (mb_strpos($sClassName, '\\') === false) {
            return false;
        }

        $sFullFilePath = RELEASEPATH . str_ireplace('\\', \DIRECTORY_SEPARATOR, mb_substr($sClassName, 7)) . '.php';

        if (!file_exists($sFullFilePath)) {
            return false;
        }
        /** @noinspection PhpIncludeInspection */
        require_once $sFullFilePath;

        return true;
    }
}
