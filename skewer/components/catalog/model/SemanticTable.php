<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm;
use skewer\base\orm\Query;

class SemanticTable extends orm\TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'cl_semantic';

    protected static function initModel()
    {
        ft\Entity::get('cl_semantic')
            ->clear(false)
            ->setNamespace(__NAMESPACE__)
            ->addField('parent_id', 'int', 'ID родительского товара')
            ->addField('parent_card', 'int', 'ID базовой карочки родительского товара')
            ->addField('child_id', 'int', 'ID товара')
            ->addField('child_card', 'int', 'ID базовой карочки товара')
            ->addField('semantic', 'int', 'Тип связи')
            ->addField('priority', 'int', 'Позиция (вес для сотировки)')
            ->addDefaultProcessorSet()
            ->selectFields(['parent_id', 'parent_card', 'child_id', 'child_card', 'semantic'])
            ->addIndex('unique')
            ->save();
    }

    /**
     * Добавление связи.
     *
     * @param $iSemantic
     * @param $iGoodId
     * @param $iGoodsCard
     * @param $iParentId
     * @param $iParentCard
     * @param mixed $iPriority
     *
     * @return bool
     */
    public static function link($iSemantic, $iParentId, $iGoodsCard, $iGoodId, $iParentCard, $iPriority = 1)
    {
        return (bool) Query::InsertInto(self::$sTableName)
            ->set('semantic', $iSemantic)
            ->set('parent_id', $iParentId)
            ->set('parent_card', $iParentCard)
            ->set('child_id', $iGoodId)
            ->set('child_card', $iGoodsCard)
            ->set('priority', $iPriority)
            ->get();
    }

    /**
     * Получение приоритета для нового сопутствующего.
     *
     * @param $iGoodId
     * @param mixed $sTypeSemantic
     *
     * @return int
     */
    public static function priorityRelated($iGoodId, $sTypeSemantic)
    {
        $oMax = Query::SQL(sprintf('SELECT MAX(priority) FROM %s WHERE `child_id`=%d AND `semantic`=%s', self::$sTableName, $iGoodId, $sTypeSemantic));
        if ($sMax = $oMax->fetchArray()) {
            return ++$sMax['MAX(priority)'];
        }

        return 1;
    }

    /**
     * Изменение позиций для сопутствующих.
     *
     * @param mixed $iGoods
     * @param mixed $idRelated
     * @param mixed $iPos
     */
    public static function changePosition($iGoods, $idRelated, $iPos)
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('priority', (int) $iPos)
            ->where('parent_id', (int) $iGoods)
            ->where('child_id', (int) $idRelated)
            ->get();
    }

    /**
     * Соортировка сопутствующих внутри раздела.
     *
     * @param $idRelated
     * @param $iItemId
     * @param $iPlaceId
     * @param string $sPos
     *
     * @return bool
     */
    public static function sortSwapRelated($idRelated, $iItemId, $iPlaceId, $sPos = '')
    {
        $oItem = Query::SelectFrom(self::$sTableName)
            ->where('parent_id', (int) $iItemId)
            ->where('child_id', $idRelated)
            ->getOne();

        $oTarget = Query::SelectFrom(self::$sTableName)
            ->where('child_id', $idRelated)
            ->where('parent_id', $iPlaceId)
            ->getOne();

        if (empty($oItem) || empty($oTarget)) {
            return false;
        }

        $sSortField = 'priority';

        $iItemPos = $oItem[$sSortField];
        $iTargetPos = $oTarget[$sSortField];

        // выбираем напрвление сдвига
        if ($iItemPos > $iTargetPos) {
            $iStartPos = $sPos == 'before' ? $iTargetPos - 1 : $iTargetPos;
            $iEndPos = $iItemPos;
            $iNewPos = $sPos == 'before' ? $iTargetPos : $iTargetPos + 1;
            self::shiftPosition($idRelated, $iStartPos, $iEndPos, '+');
            self::changePosition($iItemId, $idRelated, $iNewPos);
        } else {
            $iStartPos = $iItemPos;
            $iEndPos = $sPos == 'after' ? $iTargetPos + 1 : $iTargetPos;
            $iNewPos = $sPos == 'after' ? $iTargetPos : $iTargetPos - 1;
            self::shiftPosition($idRelated, $iStartPos, $iEndPos, '-');
            self::changePosition($iItemId, $idRelated, $iNewPos);
        }

        return true;
    }

    private static function shiftPosition($idRelated, $iStartPos, $iEndPos, $sSign = '+')
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('priority=priority' . $sSign . '?', 1)
            ->where('priority>?', (int) $iStartPos)
            ->where('priority<?', (int) $iEndPos)
            ->where('child_id', $idRelated)
            ->get();
    }

    /**
     * Удаление связи.
     *
     * @param $iSemantic
     * @param $iGoodId
     * @param $iGoodsCard
     * @param $iParentId
     * @param $iParentCard
     *
     * @return bool
     */
    public static function unlink($iSemantic, $iGoodId, $iGoodsCard, $iParentId, $iParentCard)
    {
        return (bool) Query::DeleteFrom(self::$sTableName)
            ->where('semantic', $iSemantic)
            ->where('parent_id', $iParentId)
            ->where('parent_card', $iParentCard)
            ->where('child_id', $iGoodId)
            ->where('child_card', $iGoodsCard)
            ->get();
    }

    /**
     * Удаление всех связей товара.
     *
     * @param $iGoodsId
     * @param $iGoodsCard
     *
     * @return bool
     */
    public static function remove($iGoodsId, $iGoodsCard)
    {
        Query::DeleteFrom(self::$sTableName)
            ->where('child_id', $iGoodsId)
            ->where('child_card', $iGoodsCard)
            ->get();

        Query::DeleteFrom(self::$sTableName)
            ->where('parent_id', $iGoodsId)
            ->where('parent_card', $iGoodsCard)
            ->get();

        return true;
    }
}
