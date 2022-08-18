<?php

namespace  skewer\build\Page\CatalogViewer\gallery;

use skewer\build\Page\Main\gallery\GalleryOnSite;

/**
 * Class GalleryOnMainCollection.
 */
class GalleryOnMainCollection extends GalleryOnSite
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'MainCollection';
    }

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
                450 => ['items' => 2],
                660 => ['items' => 3],
            ],
            'loop' => false,
            'shadow' => false,
        ];

        $aData['responsive'] = json_encode($aData['responsive']);

        return $aData;
    }
}
