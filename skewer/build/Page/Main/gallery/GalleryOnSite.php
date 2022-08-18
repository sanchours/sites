<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:57.
 */

namespace skewer\build\Page\Main\gallery;

use skewer\components\GalleryOnPage\FilePrototype;
use skewer\components\GalleryOnPage\GetGalleryEvent;

/**
 * Class GalleryOSite.
 */
class GalleryOnSite extends FilePrototype
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'Site';
    }

    public function getName()
    {
        $sShortClassName = (new \ReflectionClass($this))->getShortName();

        return \Yii::t('GalleryOnPage', $sShortClassName);
    }

    public static function getGallery(GetGalleryEvent $event)
    {
        $event->addGallery(self::className());
    }

    /**
     * Дефолтные значения.
     *
     * @return array
     */
    protected function getDefaultValues()
    {
        $aData = [
            'items' => 3,
            'slideBy' => 'page',
            'margin' => 20,
            'nav' => true,
            'dots' => false,
            'autoWidth' => false,
            'responsive' => [
                0 => ['items' => 1],
                520 => ['items' => 2],
                1024 => ['items' => 3],
                1600 => ['items' => 3],
            ],
            'loop' => false,
            'shadow' => false,
        ];

        $aData['responsive'] = json_encode($aData['responsive']);

        return $aData;
    }
}
