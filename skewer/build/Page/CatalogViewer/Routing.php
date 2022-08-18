<?php

namespace skewer\build\Page\CatalogViewer;

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
            '/*filter/(filtercond)condition' => 'list', // маршрут для фильтра
            '/*filter/(filtercond)condition/*page/page(int)',
            '/goods-alias/',
            '!response',  # результ. формы, отправляемой из списка товаров/списка коллекций
            '/goods-alias/!response', # результ. формы, отправляемой из детальной товара/элемента коллекции
            '/goods-alias/*page/page(int)', # пагинатор товаров в коллекции
            '/goods-alias/*page/page(int)/!response/', # результирующая формы, отправленной с 2+ страницы пагинатора товаров коллекции
            '/goods-alias/*tab/tab/',
            '/goods-alias/*tab/tab/*page/page(int)/',
            '/goods-alias/*tab/tab/*page/page(int)/!response/',
            '/*page/page(int)/', # страница пагинатора
            '/*page/page(int)/!response/', # результирующая страница формы, отправляемой из страницы пагинатора списка товаров
        ];
    }

    // func
}
