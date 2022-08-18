<?php

namespace skewer\build\Page\Search;

use skewer\base\router\RoutingInterface;

class Routing implements RoutingInterface
{
    public static function getRoutePatterns()
    {
        return [
            '/*page/page(int)/*date/date/',
            '/*date/date/',
            '/*page/page(int)/',
            '/!response/',
        ];
    }
}
