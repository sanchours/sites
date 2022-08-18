<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 01.09.2017
 * Time: 14:49.
 */

namespace skewer\build\Page\Main;

use skewer\components\GalleryOnPage\GetGalleryEvent;

class Api
{
    public static function className()
    {
        return get_called_class();
    }

    public static function registerGallery(GetGalleryEvent $oEvent)
    {
        $oEvent->addGalleryList([
            \skewer\build\Page\Main\gallery\GalleryOnSite::className(),
            \skewer\build\Page\Main\gallery\GalleryOnPage::className(),
        ]);
    }
}
