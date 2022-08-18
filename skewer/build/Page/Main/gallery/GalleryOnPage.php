<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:57.
 */

namespace skewer\build\Page\Main\gallery;

/**
 * Class GalleryOnPage.
 */
class GalleryOnPage extends GalleryOnSite
{
    public function getEntityName()
    {
        return 'Page';
    }

    public static function excludedParams()
    {
        return [];
    }
}
