<?php

namespace skewer\build\Page\News;

use skewer\base\router\RoutingInterface;

/**
 * @class: NewsRouting
 *
 * @Author: ArmiT, $Author$
 * @version: $Revision$
 * @date: $Date$
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
            '/news_alias/' => 'view',
            '/id(int)/' => 'viewById',
            '/*page/page(int)/*date/date/',
            '/*page/page(int)/*date/date/!response/',
            '/*date/date/',
            '/*page/page(int)/',
            '/*page/page(int)/!response/',
            '/news_alias/!response/' => 'view',
            '/id(int)/!response/' => 'viewById',
            '/!response/',
        ];
    }

    // func
}
