<?php

namespace skewer\components\catalog\field;

use skewer\components\gallery\Album;
use yii\helpers\ArrayHelper;

/**
 * Class Selectimage.
 */
class Selectimage extends Select
{
    protected function build($value, $rowId, $aParams)
    {
        $item = $this->getSubDataValue($value);
        $aData = [];
        $tab = '';

        $aValue = ArrayHelper::getValue($item, 'title', '');

        if (isset($item['image']) && $item['image']) {
            $iAlbumId = $item['image'];
            $sPhoto = Album::getFirstActiveImage($iAlbumId, 'icon');
            if (file_exists(WEBPATH . $sPhoto)) {
                $aData['image'] = $sPhoto;
                $tab = '<img src="' . $sPhoto . '"/> ' . $aValue;
            }
        }

        $html = ($aValue) ? $this->getHtmlData($aValue, 'selectimage.twig', $aData) : '';

        return [
            'value' => $value,
            'item' => $item,
            'tab' => $tab,
            'html' => $html,
        ];
    }
}
