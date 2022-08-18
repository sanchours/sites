<?php

namespace skewer\build\Page\Gallery;

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
            '/alias/' => 'showByAlias',
            '/!response/',
            '/alias/!response/' => 'showByAlias',
            '/*page/page(int)/',
            '/*page/page(int)/!response/',
        ];
    }

    // func
}// class
