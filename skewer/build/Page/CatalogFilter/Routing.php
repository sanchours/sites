<?php

namespace skewer\build\Page\CatalogFilter;

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
            '/goods-alias/',
            '/*filter/(filtercond)condition/*page/page(int)',
            '/*filter/(filtercond)condition',
        ];
    }

    // func
}
