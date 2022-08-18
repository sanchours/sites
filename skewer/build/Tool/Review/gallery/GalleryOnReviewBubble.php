<?php

namespace skewer\build\Tool\Review\gallery;

/**
 * Class GalleryOnReviewBubble.
 */
class GalleryOnReviewBubble extends GalleryOnReview
{
    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'ReviewBubble';
    }

    protected function getDefaultValues()
    {
        $aData = [
            'items' => 3,
            'slideBy' => 1,
            'margin' => 60,
            'nav' => false,
            'dots' => true,
            'autoWidth' => false,
            'autoHeight' => false,
            'responsive' => [
                0 => ['items' => 1, 'autoHeight' => true],
                768 => ['items' => 2, 'autoHeight' => true],
                980 => ['items' => 3],
            ],
            'loop' => false,
            'shadow' => false,
        ];

        $aData['responsive'] = json_encode($aData['responsive']);

        return $aData;
    }
}
