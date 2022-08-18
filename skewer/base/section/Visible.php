<?php

namespace skewer\base\section;

/**
 * Вспомогательный класс для работы с видимостью разделов.
 */
class Visible
{
    /**
     * Скрыт из меню - как видимый, но в меню отсутствует
     *
     * ~~~
     * В меню: -
     * search: +
     * sitemap: +
     * вложенное: +
     * прямая ссылка: +
     * ~~~
     */
    const HIDDEN_FROM_MENU = 0;

    /**
     * Виден.
     *
     * ~~~
     * В меню: +
     * search: +
     * sitemap: +
     * вложенное: +
     * прямая ссылка: +
     * ~~~
     */
    const VISIBLE = 1;

    /**
     * Скрыт из пути.
     *
     * ~~~
     * В меню: -
     * search: -
     * sitemap: -
     * вложенное: +, только разделы
     * прямая ссылка: -
     * ~~~
     */
    const HIDDEN_FROM_PATH = 2;

    /**
     * Скрыт от индексации.
     *
     * ~~~
     * В меню: -
     * search: -
     * sitemap: -
     * вложенное: -, 301 на главную
     * прямая ссылка: -, 301 на главную
     * ~~~
     */
    const HIDDEN_NO_INDEX = 3;

    /**
     * Список статусов разделов, выводимых в меню.
     *
     * @var array
     */
    public static $aShowInMenu = [
        self::VISIBLE,
        self::HIDDEN_FROM_PATH,
    ];

    /**
     * Список статусов разделов, открываемых по прямой ссылке,
     *      если есть урл и открыте не запрещено статусом
     *
     * @var array
     */
    public static $aOpenByLink = [
        self::VISIBLE,
        self::HIDDEN_FROM_MENU,
    ];

    /**
     * Список названий типов видимости.
     *
     * @return array
     */
    public static function getVisibilityTypesTitle()
    {
        return [
            self::VISIBLE => \Yii::t('tree', 'visibleVisible'),
            self::HIDDEN_FROM_MENU => \Yii::t('tree', 'visibleHiddenFromMenu'),
            self::HIDDEN_FROM_PATH => \Yii::t('tree', 'visibleHiddenFromPath'),
            self::HIDDEN_NO_INDEX => \Yii::t('tree', 'visibleHiddenFromIndex'),
        ];
    }
}
