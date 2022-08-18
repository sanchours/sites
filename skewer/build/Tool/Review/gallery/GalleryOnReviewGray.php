<?php

namespace skewer\build\Tool\Review\gallery;

/**
 * Class GalleryOnReview.
 */
class GalleryOnReviewGray extends GalleryOnReview
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'ReviewGray';
    }

    protected function getDefaultValues()
    {
        $aData = [
            'items' => 3,
            'slideBy' => 1,
            'margin' => 35,
            'nav' => false,
            'dots' => true,
            'autoWidth' => false,
            'autoHeight' => false,
            'responsive' => [
                0 => ['items' => 1, 'autoHeight' => true],
                768 => ['items' => 2, 'autoHeight' => true],
                980 => ['items' => 2, 'autoHeight' => true],
            ],
            'loop' => false,
            'shadow' => false,
        ];

        $aData['responsive'] = json_encode($aData['responsive']);

        return $aData;
    }
}
