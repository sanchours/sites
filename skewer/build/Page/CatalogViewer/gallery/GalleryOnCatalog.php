<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:57.
 */

namespace  skewer\build\Page\CatalogViewer\gallery;

use skewer\build\Page\Main\gallery\GalleryOnSite;

/**
 * Class GalleryOnCatalog.
 */
class GalleryOnCatalog extends GalleryOnSite
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'Catalog';
    }
}
