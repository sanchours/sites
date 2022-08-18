<?php

namespace skewer\build\Page\Dict;

use skewer\base\router\RoutingInterface;


class Routing implements RoutingInterface {
    /**
     * Возвращает паттерны разбора URL
     * @static
     * @return bool | array
     */
    public static function getRoutePatterns() {

        return array(
            '/dict_alias/' => 'viewByAlias',
            '/dict_id(int)/' => 'viewById',
            '/*page/page(int)/',
            '/*page/page(int)/!response/',
            '/dict_alias/!response/' => 'viewByAlias',
            '/dict_id(int)/!response/' => 'viewById',
            '/!response/'
        );
    }

}
