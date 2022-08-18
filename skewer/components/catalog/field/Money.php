<?php

namespace skewer\components\catalog\field;

use skewer\base\orm\state\StateSelect;
use skewer\base\SysVar;
use skewer\components\filters\FilteredInterface;
use skewer\components\filters\widgets\NumSlider;
use yii\helpers\ArrayHelper;

class Money extends Prototype implements FilteredInterface
{
    protected function build($value, $rowId, $aParams)
    {
        $out = ($value == (int) $value) ? (int) $value : $value;
        $html = ($value) ? $this->getHtmlData($value, 'money.twig') : '';

        return [
            'value' => $out,
            'value_full' => $value,
            'editor' => ['minValue' => 0],
            'html' => $html,
        ];
    }

    public function addFilterConditionToQuery(StateSelect $oQuery, $aFilterData = [])
    {
        $value = ArrayHelper::getValue($aFilterData, $this->getName(), 0);

        if ($value) {
            $sName = $this->getName();
            $oQuery->where("{$sName} BETWEEN ?", [$value['min'], $value['max']]);

            return true;
        }

        return false;
    }

    public function getFilterWidgetName()
    {
        return NumSlider::getTypeWidget();
    }

    public function getInputMaskOptions()
    {
        return SysVar::get('catalog.hide_price_fractional')
            ? "'alias': 'integer', 'rightAlign': false, 'allowMinus': false"
            : "'alias': 'decimal', 'rightAlign': false, 'allowMinus': false";
    }
}
