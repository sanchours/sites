<?php

namespace skewer\build\Page\Auth;

use skewer\base\router\RoutingInterface;

class Routing implements RoutingInterface
{
    public static function getRoutePatterns()
    {
        return [
            '/cmd/',
            '/!response/',
        ];
    }

    // func
}
