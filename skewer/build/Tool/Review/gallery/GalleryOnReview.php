<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:57.
 */

namespace skewer\build\Tool\Review\gallery;

use skewer\build\Page\Main\gallery\GalleryOnSite;

/**
 * Class GalleryOnReview.
 */
class GalleryOnReview extends GalleryOnSite
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'Review';
    }
}
