<?php

namespace skewer\components\catalog\field;

use skewer\base\orm\state\StateSelect;
use skewer\components\filters\FilteredInterface;
use skewer\components\filters\widgets;
use yii\helpers\ArrayHelper;

class StringField extends Prototype implements FilteredInterface
{
    protected function build($value, $rowId, $aParams)
    {
        $html = ($value) ? $this->getHtmlData($value) : '';

        return [
            'value' => $value,
            'tab' => $value,
            'html' => $html,
        ];
    }

    public function addFilterConditionToQuery(StateSelect $oQuery, $aFilterData = [])
    {
        $value = ArrayHelper::getValue($aFilterData, $this->getName(), []);

        $value = reset($value);

        if ($value) {
            $sName = $this->getName();
            $oQuery->like($sName, $value);

            return true;
        }

        return false;
    }

    public function getFilterWidgetName()
    {
        return widgets\Input::getTypeWidget();
    }
}
