<?php

namespace skewer\components\catalog\field;

use skewer\components\gallery\Album;
use yii\helpers\ArrayHelper;

/**
 * Class Multiselectimage.
 */
class Multiselectimage extends Multiselect
{
    protected function build($value, $rowId, $aParams)
    {
        $value = $this->ftField->getLinkRow($rowId);

        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $items = array_map(function ($item) {
            return $this->getSubDataValue($item);
        }, $value);

        $aHtmlImages = array_map(static function ($item) {
            $aValue = ArrayHelper::getValue($item, 'title', '');
            if (isset($item['image']) && $item['image']) {
                $iAlbumId = $item['image'];
                $sPhoto = Album::getFirstActiveImage($iAlbumId, 'icon');
                if (file_exists(WEBPATH . $sPhoto)) {
                    $aValue = '<img src="' . $sPhoto . '"/> ' . $aValue;
                }
            }

            return $aValue;
        }, $items);

        $sHtmlImages = implode(', ', $aHtmlImages);
        $html = ($sHtmlImages) ? $this->getHtmlData(
            $value,
            'multiselectimage.twig',
            ['sHtmlImages' => $sHtmlImages]
        )
            : '';

        return [
            'value' => $value,
            'item' => $items,
            'tab' => $sHtmlImages,
            'html' => $html,
        ];
    }
}
