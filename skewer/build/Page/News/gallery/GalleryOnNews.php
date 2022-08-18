<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:57.
 */

namespace skewer\build\Page\News\gallery;

use skewer\build\Page\Main\gallery\GalleryOnSite;

/**
 * Class GalleryOnNews.
 */
class GalleryOnNews extends GalleryOnSite
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'News';
    }
}
