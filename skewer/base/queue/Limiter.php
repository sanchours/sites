<?php

namespace skewer\base\queue;

/**
 * Класс, отвечающий за слежение за ограничениями по ресурсам
 * Сейчас смотрит только за временем
 */
class Limiter
{
    private static $instance = null;

    private $time = 0;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Limiter();
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init()
    {
        $this->time = microtime(true);
    }

    /**
     * Проверка на возможность продолжения работы
     * Возвращает true, если работу можно продолжать, false, если работу нужно остановить.
     *
     * @return bool
     */
    public static function checkLimit()
    {
        $oLimiter = self::getInstance();

        $maxTime = MAX_EXECUTION_TIME * 0.6;

        $currentTime = microtime(true) - $oLimiter->time;

        return $currentTime < $maxTime;
    }
}
