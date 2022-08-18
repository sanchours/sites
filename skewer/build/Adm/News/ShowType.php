<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 03.03.2017
 * Time: 15:14.
 */

namespace skewer\build\Adm\News;

class ShowType
{
    /** галерея отключена */
    const DISABLE = 0;

    /** галерея на детальном */
    const DETAIL = 1;

    /** галерея в кратком описании */
    const PREVIEW = 2;

    /** галерея и в кратком и на детальной */
    const BOTH = 3;

    /**
     * Вохвращает список статусов галереи.
     *
     * @return array список статусов галереи
     */
    public static function getGalleryStatusList()
    {
        return [
            self::DISABLE => \Yii::t('news', 'gallery_disable'),
            self::DETAIL => \Yii::t('news', 'gallery_detail'),
            self::PREVIEW => \Yii::t('news', 'gallery_preview'),
            self::BOTH => \Yii::t('news', 'gallery_both'),
        ];
    }
}
