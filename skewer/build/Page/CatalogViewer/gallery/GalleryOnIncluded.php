<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:57.
 */

namespace skewer\build\Page\CatalogViewer\gallery;

/**
 * Class GalleryOnIncluded.
 */
class GalleryOnIncluded extends GalleryOnCatalog
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'Included';
    }
}
