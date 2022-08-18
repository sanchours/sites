<?php

namespace skewer\build\Page\Articles;

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
            '/articles_alias/',
            '/articles_id(int)/',
            '/*page/page(int)/*date/date/',
            '/*page/page(int)/*date/date/!response/',
            '/*date/date/',
            '/*page/page(int)/',
            '/*page/page(int)/!response/',
            '/!response/',
            '/articles_alias/!response/',
            '/articles_id(int)/!response/',
        ];
    }

    // func
}
