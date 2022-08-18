<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm;
use skewer\base\orm\Query;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Visible;

class GoodsTable extends orm\TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'c_goods';

    protected static function initModel()
    {
        ft\Entity::get('c_goods')
            ->clear(false)
            ->setNamespace(__NAMESPACE__)
            ->addField('base_id', 'int', 'ID товарной позиции')
            ->addField('base_card_id', 'int', 'ID базовой карточки')
            ->addField('base_card_name', 'varchar(255)', 'Название базовой карточки')
            ->addField('ext_card_id', 'int', 'ID расширенной карточки')
            ->addField('ext_card_name', 'varchar(255)', 'Название расширенной карточки')
            ->addField('parent', 'int', 'Главный товар')
            ->addField('section', 'int', 'Главный раздел')
            ->addField('__add_date', 'date', 'Дата добавления')
            ->addField('__upd_date', 'date', 'Дата редактирования')
            ->addField('modific_priority', 'int', '')
            ->addDefaultProcessorSet()
            ->selectFields(['base_id', 'base_card_id'])
            ->addIndex('unique', 'prim')
            ->save();
    }

    /**
     * Получение записи о товаре.
     *
     * @param int $iBaseCardId
     *
     * @return array|bool|orm\ActiveRecord
     */
    public static function get($iBaseCardId)
    {
        return Query::SelectFrom(self::$sTableName)
            ->where('base_id', $iBaseCardId)
            ->getOne();
    }

    public static function add($iGoodId, $iGoodsCard, $sGoodsCard, $iExGoodsCard, $sExGoodsCard, $iGoodsParent = 0, $iSectionId = 0, $priority = 0)
    {
        if (!$iGoodsParent) {
            $iGoodsParent = $iGoodId;
        }

        return (bool) Query::InsertInto(self::$sTableName)
            ->set('base_id', $iGoodId)
            ->set('base_card_id', $iGoodsCard)
            ->set('base_card_name', $sGoodsCard)
            ->set('ext_card_id', $iExGoodsCard)
            ->set('ext_card_name', $sExGoodsCard)
            ->set('parent', $iGoodsParent)
            ->set('section', $iSectionId)
            ->set('__add_date', date('Y-m-d H:i:s'))
            ->set('__upd_date', date('Y-m-d H:i:s'))
            ->set('modific_priority', $priority)
            ->get();
    }

    public static function remove($iGoodId, $iGoodsCard)
    {
        return (bool) Query::DeleteFrom(self::$sTableName)
            ->where('base_id', $iGoodId)
            ->where('base_card_id', $iGoodsCard)
            ->get();
    }

    public static function setChangeDate($iGoodId, $iGoodsCard)
    {
        return (bool) Query::UpdateFrom(self::$sTableName)
            ->set('__upd_date', date('Y-m-d H:i:s'))
            ->where('base_id', $iGoodId)
            ->where('base_card_id', $iGoodsCard)
            ->get();
    }

    public static function getChangeDate($iGoodId, $iGoodsCard)
    {
        $aRow = Query::SelectFrom(self::$sTableName)
            ->fields('__upd_date')
            ->where('base_id', $iGoodId)
            ->where('base_card_id', $iGoodsCard)
            ->getOne();

        return $aRow ? $aRow['__upd_date'] : false;
    }

    /**
     * Для списка товаров определяет кол-во дочерних.
     *
     * @param array|int $list
     *
     * @return array
     */
    public static function getChildCount($list)
    {
        $query = Query::SelectFrom(self::$sTableName)
            ->fields('parent, count(base_id) as cnt', true)
            ->where('parent', $list)
            ->andWhere('parent!=base_id')
            ->groupBy('parent')
            ->asArray();

        $res = [];

        while ($row = $query->each()) {
            $res[$row['parent']] = (int) $row['cnt'];
        }

        return $res;
    }

    /**
     * Возвращает id текущего главного раздела товара.
     *
     * @param $iGoodsId
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function getMainSection($iGoodsId)
    {
        $query = Query::SelectFrom(self::$sTableName)
            ->fields('section')
            ->where('base_id', $iGoodsId)

            // Проверить раздел на существование и видимость
            ->join('inner', TreeSection::tableName(), 'tbl_tree_section', 'section = tbl_tree_section.id')
            ->where('tbl_tree_section.visible IN ?', Visible::$aOpenByLink)
            ->where('tbl_tree_section.link = ?', '')

            ->asArray();

        return ($row = $query->getOne()) ? $row['section'] : false;
    }

    /**
     * @param $iGoodsId
     * @param $iSectionId
     *
     * @return bool
     */
    public static function setMainSection($iGoodsId, $iSectionId)
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('section', $iSectionId)
            ->where('parent', $iGoodsId)
            ->orWhere('base_id', $iGoodsId)
            ->get();

        return true;
    }

    /**
     * Для товаров с таким главным разделом - выставить занчение 0
     * Потом они должны быть восстановлены.
     *
     * @param int $iSectionId
     *
     * @return bool
     */
    public static function removeSection($iSectionId)
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('section', 0)
            ->where('section', $iSectionId)
            ->get();

        return true;
    }

    /**
     * Удаление всех товаров по карточке.
     *
     * @param $iBaseCard
     * @param int $iExtCard
     *
     * @return bool
     */
    public static function removeCard($iBaseCard, $iExtCard = 0)
    {
        $query = new \yii\db\Query();

        $aGoods = $query->select('base_id')
            ->from(self::$sTableName)
            ->where(['ext_card_id' => $iExtCard])
            ->all();

        $aGoodIds = [];
        if (is_array($aGoods) && $aGoods) {
            foreach ($aGoods as $good) {
                if (is_array($good) && (isset($good['base_id']))) {
                    $aGoodIds[] = $good['base_id'];
                }
            }
        }

        $query = Query::DeleteFrom(self::$sTableName);

        if ($iBaseCard) {
            $query->where('base_card_id', $iBaseCard);
        }

        if ($iExtCard) {
            $query->where('ext_card_id', $iExtCard);
        }

        $query->get();

        \Yii::$app
            ->db
            ->createCommand()
            ->delete('search_index', [
                'class_name' => 'CatalogViewer',
                'object_id' => $aGoodIds,
            ])
            ->execute();

        return true;
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return (new \yii\db\Query())->select('MAX(`__upd_date`) as max')->from(self::$sTableName)->one();
    }

    /**
     * Сортировка товарных позиций внутри раздела.
     *
     * @param $iSectionId
     * @param $iItemId
     * @param $iPlaceId
     * @param string $sPos
     * @param mixed $iParentId
     *
     * @return bool
     */
    public static function sortSwap($iParentId, $iItemId, $iPlaceId, $sPos = '')
    {
        $oItem = Query::SelectFrom(self::$sTableName)
            ->where('base_id', $iItemId)
            ->where('parent', (int) $iParentId)
            ->getOne();

        $oTarget = Query::SelectFrom(self::$sTableName)
            ->where('base_id', $iPlaceId)
            ->where('parent', (int) $iParentId)
            ->getOne();

        if (empty($oItem) || empty($oTarget)) {
            return false;
        }

        $sSortField = 'modific_priority';

        $iItemPos = $oItem[$sSortField];
        $iTargetPos = $oTarget[$sSortField];

        // выбираем напрвление сдвига
        if ($iItemPos > $iTargetPos) {
            $iStartPos = $sPos == 'before' ? $iTargetPos - 1 : $iTargetPos;
            $iEndPos = $iItemPos;
            $iNewPos = $sPos == 'before' ? $iTargetPos : $iTargetPos + 1;
            self::shiftPosition($iParentId, $iStartPos, $iEndPos, '+');
            self::changePosition($iItemId, $iParentId, $iNewPos);
        } else {
            $iStartPos = $iItemPos;
            $iEndPos = $sPos == 'after' ? $iTargetPos + 1 : $iTargetPos;
            $iNewPos = $sPos == 'after' ? $iTargetPos : $iTargetPos - 1;
            self::shiftPosition($iParentId, $iStartPos, $iEndPos, '-');
            self::changePosition($iItemId, $iParentId, $iNewPos);
        }

        return true;
    }

    private static function shiftPosition($iParentId, $iStartPos, $iEndPos, $sSign = '+')
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('modific_priority=modific_priority' . $sSign . '?', 1)
            ->where('modific_priority>?', (int) $iStartPos)
            ->where('modific_priority<?', (int) $iEndPos)
            ->where('parent', (int) $iParentId)
            ->get();
    }

    private static function changePosition($iGoods, $iParentId, $iPos)
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('modific_priority', (int) $iPos)
            ->where('base_id', (int) $iGoods)
            ->where('parent', (int) $iParentId)
            ->get();
    }
}
