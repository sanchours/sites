<?php

namespace skewer\build\Page\Profile;

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
            '/cmd/',
            '/*page/page(int)/' => 'init',
            '/cmd/*page/page(int)/',
            '/*detail/id(int)/' => 'detail',
            '/!response/',
        ];
    }

    // func
}
