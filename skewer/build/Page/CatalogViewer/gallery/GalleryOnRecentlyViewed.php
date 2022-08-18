<?php

namespace  skewer\build\Page\CatalogViewer\gallery;

/**
 * Class GalleryOnRecentlyViewed.
 */
class GalleryOnRecentlyViewed extends GalleryOnCatalog
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'RecentlyViewed';
    }
}
