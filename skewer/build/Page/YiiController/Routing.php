<?php

namespace skewer\build\Page\YiiController;

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
            '/controller/',
            '/controller/action/',
        ];
    }
}
