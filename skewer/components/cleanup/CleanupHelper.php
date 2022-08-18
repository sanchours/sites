<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 07.11.2018
 * Time: 10:24.
 */

namespace skewer\components\cleanup;

class CleanupHelper
{
    public static function printMessage($message, $nel = true)
    {
        echo $message;
        if ($nel) {
            echo "\r\n";
        }
    }

    public static function addNEL()
    {
        return " \r\n";
    }
}
