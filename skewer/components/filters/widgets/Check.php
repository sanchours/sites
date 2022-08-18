<?php

namespace skewer\components\filters\widgets;

use skewer\components\filters\IndexedFilter;

/**
 * Class Check - виджет галочка.
 */
class Check extends Prototype
{
    public function parse($aFilterFieldData)
    {
        $valueBox = reset($aFilterFieldData);

        if ($valueBox === 'yes') {
            $valueBox = 1;
        } else {
            $valueBox = 0;
        }

        $arr = $this->oFilter->getUniqueValues4FilterField($this->oField);

        if (!$arr) {
            return [];
        }

        if (count($arr) == 1) {
            return [];
        }

        $clear_arr = $this->oFilter->getUniqueValues4FilterField($this->oField, true);

        if (count($clear_arr)) {
            if (!$valueBox) { //При не выбранном флажке
                $bDisable = !in_array(1, $clear_arr);
            } else {
                $bDisable = !in_array($valueBox, $clear_arr);
            }
        } else {
            $bDisable = true;
        }

        $aOut = [
            'title' => $this->getFieldTitle(),
            'name' => $this->getFieldName(),
            'type' => self::getTypeWidget(),
            'disable' => !$this->oFilter->isShadowParams() ? false : $bDisable,
            'check' => (bool) $valueBox,
        ];

        if ($this->oFilter instanceof IndexedFilter) {
            $aData4Url = $this->oFilter->buildData4FilterValue($this->getFieldName(), 'yes');
            $sUrl = $this->oFilter->buildUrlByFilterData($aData4Url);
            $bCanIndex = $this->oFilter->canIndexUrlByFilterData($aData4Url);

            $sItemTitle = \Yii::t('catalog', $this->getFieldName() . '_title_in_filter');

            if (($sItemTitle == $this->getFieldName() . '_title_in_filter')) {
                $sItemTitle = \Yii::t('page', 'yes');
            }

            $aOut += [
                'url' => $sUrl,
                'canIndex' => $bCanIndex && !$bDisable,
                'itemTitle' => $sItemTitle,
            ];
        }

        return $aOut;
    }

    public static function getTypeWidget()
    {
        return 'check';
    }

    public function convertValueToTitle($aDataItem)
    {
        return $this->getFieldTitle();
    }
}
