<?php

namespace skewer\components\site_tester;

use skewer\base\site\Server;
use skewer\components\gateway;

class Api
{
    /**
     * Режимы работы.
     */
    const MODE_PROD = 'prod';
    const MODE_DEV = 'dev';

    /**
     * Статусы.
     */
    const MESSAGE_TYPE_WARNING = 'warning';
    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_INFO = 'info';

    const SESSION = 'sitetester';

    /**
     * @throws gateway\Exception
     *
     * @return string
     */
    public static function getSiteMode()
    {
        return (Server::isProduction()) ? self::MODE_PROD : self::MODE_DEV;
    }

    /**
     * @return array
     */
    public function getProdTestList()
    {
        return [
            tests\Robots::getName(),
            tests\DisplayErrors::getName(),
        //	Tests\Chmod::getName(),
            tests\Chown::getName(),
        ];
    }

    /**
     * @return array
     */
    public function getDevTestList()
    {
        return [
            tests\Robots::getName(),
            tests\DisplayErrors::getName(),
        //	Tests\Chmod::getName(),
            tests\Chown::getName(),
        ];
    }

    public static function getStatus($name)
    {
        return  (isset($_SESSION[self::SESSION][$name])) ? $_SESSION[self::SESSION][$name]['status'] : 'undefined';
    }

    public static function getInfo($name)
    {
        return  (isset($_SESSION[self::SESSION][$name])) ? $_SESSION[self::SESSION][$name] : false;
    }
}
