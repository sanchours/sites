<?php

namespace skewer\components\catalog\field;

use skewer\base\ft\Relation;
use skewer\base\orm\state\StateSelect;
use skewer\components\catalog\Card;
use skewer\components\catalog\Dict;
use skewer\components\filters\FilteredInterface;
use skewer\components\filters\widgets;
use yii\helpers\ArrayHelper;

class Multiselect extends Prototype implements FilteredInterface
{
    public $isLinked = true;

    public $isSpecialEdit = true;

//    // получение данных для поля со сложными связями. подумать над переносом в другое место
//    $oRel = $oField->getModel()->getOneFieldRelation( $oField->getName() );
//    if ( $oRel and $oRel->getType() == ft\Relation::MANY_TO_MANY ) {
//        $aFieldData[$sFieldName] = $oField->getLinkRow( $out['id'] );
//    }

    protected function build($value, $rowId, $aParams)
    {
        $value = $this->ftField->getLinkRow($rowId);

        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $items = [];
        $out = '';

        foreach ($value as $val) {
            if ($value) {
                $item = $this->getSubDataValue($val);
                $items[] = $item;
                $out .= ($out ? ', ' : '') . ArrayHelper::getValue($item, 'title', '');
            }
        }
        $html = ($out) ? $this->getHtmlData($out) : '';

        return [
            'value' => $value,
            'item' => $items,
            'tab' => $out,
            'html' => $html,
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
            $oQuery
                ->join('inner', $this->ftField->getLinkTableName(), $this->ftField->getLinkTableName(), 'co_' . Card::DEF_BASE_CARD . '.id=`' . $this->ftField->getLinkTableName() . '`.' . Relation::INNER_FIELD)
                ->on(sprintf('%s.%s', $this->ftField->getLinkTableName(), Relation::EXTERNAL_FIELD), $value);

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
