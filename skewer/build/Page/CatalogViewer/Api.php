<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 01.09.2017
 * Time: 14:18.
 */

namespace skewer\build\Page\CatalogViewer;

use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\build\Catalog\Collections;
use skewer\components\GalleryOnPage\GetGalleryEvent;

class Api
{
    public static function className()
    {
        return get_called_class();
    }

    public static function registerGallery(GetGalleryEvent $oEvent)
    {
        $aGalleries = [
            gallery\GalleryOnCatalog::className(),
            gallery\GalleryOnIncluded::className(),
            gallery\GalleryOnMainCatalog::className(),
            gallery\GalleryOnRecentlyViewed::className(),
            gallery\GalleryOnRelated::className(),
        ];

        if (\Yii::$app->register->moduleExists(Collections\Module::getNameModule(), Layer::CATALOG)) {
            array_push($aGalleries, gallery\GalleryOnMainCollection::className());
        }

        $oEvent->addGalleryList($aGalleries);
    }

    /**
     * @return bool
     */
    public static function checkQuickView()
    {
        return (bool) SysVar::get('catalog.quick_view_show');
    }
}
