<?php

namespace skewer\build\Tool\LeftList;

/**
 * Класс для упрвления группами модулей в панели управления.
 */
class Group
{
    /** настройки контента сайта */
    const CONTENT = 'content';

    /** административные параметры */
    const ADMIN = 'admin';

    /** системные настройки */
    const SYSTEM = 'system';

    /** Языковая группа */
    const LANGUAGE = 'language';

    /** заказы */
    const ORDER = 'order';

    /**@const настройки seo */
    const SEO = 'seo';

    /**
     * Отдает название группы по идентификатору.
     *
     * @param $sGroup
     *
     * @return string
     */
    public static function getTitle($sGroup)
    {
        return \Yii::t('adm', 'group_' . (string) $sGroup);
    }
}
