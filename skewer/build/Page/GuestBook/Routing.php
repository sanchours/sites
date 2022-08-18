<?php

namespace skewer\build\Page\GuestBook;

use skewer\base\router\RoutingInterface;

/**
 * Класс задание шаблонов роутинга для ЧПУ
 * Class Routing.
 */
class Routing implements RoutingInterface
{
    /**
     * Возвращает паттерны разбора URL.
     *
     * @static
     *
     * @return bool | array
     */
    public static function getRoutePatterns()
    {
        return [
            '*page/page(int)/',
            '*page/page(int)/!response/',
        ];
    }

    // func
}
