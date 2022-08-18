<?php

namespace skewer\components\catalog\field;

use skewer\base\orm\state\StateSelect;
use skewer\components\catalog\Card;
use skewer\components\catalog\Dict;
use skewer\components\filters\FilteredInterface;
use skewer\components\filters\widgets;
use yii\helpers\ArrayHelper;

class Select extends Prototype implements FilteredInterface
{
    public $isLinked = true;

    public $isSpecialEdit = true;

    protected function build($value, $rowId, $aParams)
    {
        $item = $this->getSubDataValue($value);
        $aValue = ArrayHelper::getValue($item, 'title', '');
        $html = ($aValue) ? $this->getHtmlData($aValue) : '';

        return [
            'value' => $value,
            'item' => $item,
            'tab' => ArrayHelper::getValue($item, 'title', ''),
            'html' => $html,
        ];
    }

    public static function getGroupWidgetList($link_id = '')
    {
        return [
            widgets\CheckGroup::getTypeWidget() => \Yii::t('Card', 'widget_check_group'),
            widgets\Select::getTypeWidget() => \Yii::t('Card', 'widget_select'),
        ];
    }

    public static function getEntityList($link_id = '')
    {
        return Dict::getDictAsArray(Card::DEF_GOODS_MODULE);
    }

    public function addFilterConditionToQuery(StateSelect $oQuery, $aFilterData = [])
    {
        $value = ArrayHelper::getValue($aFilterData, $this->getName());

        if ($value) {
            $oQuery->where($this->getName(), $value);

            return true;
        }

        return false;
    }

    public function getFilterWidgetName()
    {
        if ($this->getWidget()) {
            return $this->getWidget();
        }

        return widgets\CheckGroup::getTypeWidget();
    }
}
