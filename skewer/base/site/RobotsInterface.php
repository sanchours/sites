<?php

namespace skewer\base\site;

/**
 * Прототип для правил для robots.txt.
 */
interface RobotsInterface
{
    /**
     * Возвращает паттерны для robots.txt для запрета страниц.
     *
     * @static
     *
     * @return bool | array
     */
    public static function getRobotsDisallowPatterns();

    /**
     * Возвращает паттерны для robots.txt для разрешения страниц.
     *
     * @static
     *
     * @return bool | array
     */
    public static function getRobotsAllowPatterns();
}// interface
