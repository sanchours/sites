<?php

namespace skewer\components\catalog\field;

use skewer\base\ft\Relation;
use skewer\base\orm\state\StateSelect;
use skewer\components\catalog\Card;
use skewer\components\catalog\Parser;
use skewer\components\ext\FormView;
use yii\helpers\ArrayHelper;

class Multicollection extends Collection
{
    protected $subSection = 0;

    public $isLinked = true;

    public $isSpecialEdit = true;

    /**
     * @param string $values
     * @param int $rowId
     * @param array $aParams
     *
     * @return array
     */
    protected function build($values, $rowId, $aParams)
    {
        if (is_string($values) and empty($values)) {
            $values = $this->ftField->getLinkRow($rowId);
        }

        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        $items = [];
        foreach ($values as $value) {
            $items[] = $this->getSubDataValue($value);
        }

        $aOut = [];
        foreach ($items as &$item) {
            $iSectionId = $this->subSection;

            $href = false;
            if ($item['active']) {
                $href = Parser::buildUrl($iSectionId, $rowId, $item['alias']);
            }

            $sAltTitle = (isset($item['alt_title']) && $item['alt_title']) ? $item['alt_title'] : $item['title'];
            $item['html'] = $href ? ' <a href="' . $href . '">' . $sAltTitle . '</a>' : $sAltTitle;
            $aOut[] = $href ? ' <a href="' . $href . '">' . $sAltTitle . '</a>' : $sAltTitle;
        }

        $aImplode = implode(',', $aOut);
        $html = ($aImplode) ? $this->getHtmlData($aImplode, 'multicollection.twig') : '';

        return [
            'value' => implode(',', $values),
            'item' => $items,
            'tab' => $aImplode,
            'html' => $html,
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
            $oQuery
                ->join('inner', $this->ftField->getLinkTableName(), $this->ftField->getLinkTableName(), 'co_' . Card::DEF_BASE_CARD . '.id=`' . $this->ftField->getLinkTableName() . '`.' . Relation::INNER_FIELD)
                ->on(sprintf('%s.%s', $this->ftField->getLinkTableName(), Relation::EXTERNAL_FIELD), $value);

            return true;
        }

        return false;
    }
}
