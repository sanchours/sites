<?php
/**
 * Logger класс
 * Журналирование.
 *
 * @version 1.0001.28.01.11 13:52
 * @date 28.01.11 13:52
 *
 * @author ArmiT(artem.rar@gmail.com)
 */

namespace skewer\base\log;

use Throwable;
use Yii;

class Logger
{
    /**
     * @static Вывод информации в файл
     */
    public static function dump()
    {
        if (!func_num_args()) {
            return;
        }

        foreach (func_get_args() as $item) {
            Yii::accessLog($item);
        }
    }

    public static function error()
    {
        if (!func_num_args()) {
            return;
        }

        foreach (func_get_args() as $item) {
            Yii::error($item);
        }
    }

    public static function dumpTask()
    {
        if (!func_num_args()) {
            return;
        }

        foreach (func_get_args() as $item) {
            Yii::getLogger()->log($item, \yii\log\Logger::LEVEL_INFO, 'task');
        }
    }

    /**
     * Заносит в лог исключение.
     *
     * @param Throwable $e
     */
    public static function dumpException(Throwable $e)
    {
        self::error("\n" . (string) $e . "\n");
    }
}
