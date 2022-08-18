<?php

namespace skewer\components\seo;

interface SeoInterface
{
    /**
     * Вёрнёт название сущности.
     *
     * @return string
     */
    public static function getTitleEntity();

    /**
     * Вернет группу в таблице seo_data(поле group).
     *
     * @return string
     */
    public static function getGroup();

    /**
     * Вернёт статическую часть псевдонима шаблона.
     *
     * @return string
     */
    public static function getAlias();
}
