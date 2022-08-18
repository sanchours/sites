<?php

namespace skewer\components\catalog\field;

use skewer\base\orm\state\StateSelect;
use skewer\components\filters\FilteredInterface;
use skewer\components\filters\widgets;
use yii\helpers\ArrayHelper;

class Check extends Prototype implements FilteredInterface
{
    private $aSystemName = ['discount' => 'image_sale', 'hit' => 'image_hit', 'new' => 'image_new'];

    protected function build($value, $rowId, $aParams)
    {
        if (isset($this->aSystemName[$this->name])) {
            $sTmp = 'flag.twig';
            $aData = ['img' => $this->aSystemName[$this->name]];
        } else {
            $sTmp = 'check.twig';
            $aData = [];
        }
        $html = $this->getHtmlData($value, $sTmp, $aData);

        return [
            'value' => $value,
            'tab' => $value,
            'html' => $html,
        ];
    }

    public function addFilterConditionToQuery(StateSelect $oQuery, $aFilterData = [])
    {
        $aValue = ArrayHelper::getValue($aFilterData, $this->getName(), []);

        $iValue = reset($aValue);

        if ($iValue) {
            $oQuery->where($this->getName(), 1);

            return true;
        }

        return false;
    }

    public function getFilterWidgetName()
    {
        return widgets\Check::getTypeWidget();
    }
}
