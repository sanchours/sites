<?php

namespace skewer\components\catalog\field;

use skewer\base\orm\state\StateSelect;
use skewer\base\section\models\ParamsAr;
use skewer\components\catalog\Card;
use skewer\components\catalog\Parser;
use skewer\components\catalog\Section;
use skewer\components\ext\FormView;
use skewer\components\filters\FilteredInterface;
use skewer\components\filters\widgets;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class Collection extends Prototype implements FilteredInterface
{
    protected $subSection = 0;

    public $isLinked = true;

    public $isSpecialEdit = true;

    protected function build($value, $rowId, $aParams)
    {
        $item = $this->getSubDataValue($value);
        $itemTitle = ArrayHelper::getValue($item, 'title', '');
        $itemAltTitle = ArrayHelper::getValue($item, 'alt_title', '');
        $itemAlias = ArrayHelper::getValue($item, 'alias', '');
        $itemActive = ArrayHelper::getValue($item, 'active', '');

        $iSectionId = $this->subSection;

        $href = false;
        if ($itemActive) {
            $href = Parser::buildUrl($iSectionId, $value, $itemAlias);
        }

        $out = $href ? ' <a href="' . $href . '">' . $itemTitle . '</a>' : $itemTitle;
        $sTitle = ($itemAltTitle) ? $itemAltTitle : $itemTitle;
        $outAlt = $href ? ' <a href="' . $href . '">' . $sTitle . '</a>' : $itemTitle;

        $html = ($out || $outAlt) ? $this->getHtmlData($value, 'collection.twig', ['html' => $out, 'htmlAlt' => $outAlt]) : '';

        return [
            'value' => $value,
            'tab' => $out,
            'item' => $item,
            'html' => $html,
        ];
    }

    protected function load()
    {
        $cardId = Card::getId($this->card);
        // получаем id сущности в текущей карточке
        $entityId = Card::getFieldByName($cardId, $this->name)->link_id;
        $this->subSection = Section::get4Collection($entityId);
    }

    /**
     * @param $oField
     *
     * @throws UserException
     */
    public static function validateFieldDelete($oField)
    {
        $iLinkId = $oField->link_id;

        $iCountUses = ParamsAr::find()
            ->where([
                'value' => $iLinkId . ':' . $oField->name,
                'name' => 'collectionField',
            ])
            ->count();

        if ($iCountUses) {
            throw new UserException(\Yii::t('catalog', 'field_collection_in_use'));
        }
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
        return FormView::markUniqueValue(\skewer\components\catalog\Collection::getCollectionList());
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
