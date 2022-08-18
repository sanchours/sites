<?php

namespace skewer\build\Tool\Review\gallery;

/**
 * Class GalleryOnSingle.
 */
class GalleryOnReviewSingle extends GalleryOnReview
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'ReviewSingle';
    }

    protected function getDefaultValues()
    {
        $aData = [
            'items' => 1,
            'slideBy' => 1,
            'margin' => 60,
            'nav' => true,
            'dots' => true,
            'autoWidth' => false,
            'autoHeight' => true,
            'responsive' => [],
            'loop' => false,
            'shadow' => false,
        ];

        $aData['responsive'] = json_encode($aData['responsive']);

        return $aData;
    }

    public static function excludedParams()
    {
        return [
            'margin',
            'autoWidth',
            'responsive',
            'items',
            'slideBy',
        ];
    }
}
