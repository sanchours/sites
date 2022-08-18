<?php

namespace skewer\build\Page\FAQ;

use skewer\base\router\RoutingInterface;

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
            '/alias/' => 'detail',
            '/id(int)/' => 'detail',
            '/*page/page(int)/',
            '!response',
        ];
    }

    // func
}
