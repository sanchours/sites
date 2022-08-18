<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm;
use skewer\base\orm\Query;
use skewer\components\catalog\Card;
use skewer\components\catalog\Entity;

class SectionTable extends orm\TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'cl_section';

    private static $cache = [];
    private static $updateCache = false;

    /**
     * @param int $updateCache
     */
    public static function setUpdateCache($updateCache)
    {
        self::$updateCache = $updateCache;
    }

    /**
     * @throws ft\exception\Model
     */
    protected static function initModel()
    {
        ft\Entity::get('cl_section')
            ->clear(false)
            ->setNamespace(__NAMESPACE__)
            ->addField('section_id', 'int', 'Раздел для показа')
            ->addField('goods_id', 'int', 'ID товара')
            ->addField('goods_card', 'int', 'Базовая карточка товара')
            ->addField('goods_ext_card', 'int', 'Карточка товара')
            ->addField('priority', 'int', 'Позиция (вес для сотировки)')
            ->addDefaultProcessorSet()
            ->selectFields(['section_id', 'goods_id', 'goods_card'])
            ->addIndex('unique', 'prim')
            ->save();
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    private static function getCache($key)
    {
        return (isset(self::$cache[$key])) ? self::$cache[$key] : [];
    }

    /**
     * @param array $cache
     * @param mixed $key
     * @param mixed $value
     */
    private static function setCache($key, $value = '')
    {
        self::$cache[$key] = $value;
    }

    /**
     * @param $iSectionId
     * @param $iGoodId
     * @param $iBaseCard
     * @param $iExtCard
     *
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public static function link($iSectionId, $iGoodId, $iBaseCard, $iExtCard)
    {
        $res = \Yii::$app->db->createCommand('SELECT max( `priority` )AS pos FROM `' . self::$sTableName . '` WHERE section_id=' . $iSectionId)->queryOne();

        return (bool) Query::InsertInto(self::$sTableName)
            ->set('section_id', $iSectionId)
            ->set('goods_id', $iGoodId)
            ->set('goods_card', $iBaseCard)
            ->set('goods_ext_card', $iExtCard)
            ->set('priority', ($res['pos'] + 1))
            ->get();
    }

    /**
     * Отвязать каталожные позиции от раздела/разделов.
     *
     * @param array|int $mSectionId Id раздела
     * @param array|int $mGoodId Id позиции
     * @param int $iBaseCard
     *
     * @return bool
     */
    public static function unlink($mSectionId, $mGoodId = 0, $iBaseCard = 0)
    {
        //надо пересобрать кеш
        self::setUpdateCache(true);

        return (bool) Query::DeleteFrom(self::$sTableName)
            ->where('section_id', $mSectionId)
            ->where('goods_id' . ($mGoodId ? '' : ' != ?'), $mGoodId)
            ->where('goods_card' . ($iBaseCard ? '' : ' != ?'), $iBaseCard)
            ->get();
    }

    public static function removeCard($iBaseCard, $iExtCard = 0)
    {
        $query = Query::DeleteFrom(self::$sTableName);

        if ($iBaseCard) {
            $query->where('goods_card', $iBaseCard);
        }

        if ($iExtCard) {
            $query->where('goods_ext_card', $iExtCard);
        }

        $query->get();

        return true;
    }

    public static function countCard($iSectionId, $iBaseCard)
    {
        $aRow = Query::SelectFrom(self::$sTableName)
            ->fields('count( DISTINCT ext_card_id ) AS cnt', true)
            ->join('left', GoodsTable::getTableName(), 'jt', 'goods_id=base_id')
            ->where('section_id', $iSectionId)
            ->where('goods_card', $iBaseCard)
            ->asArray()
            ->getOne();

        return $aRow['cnt'] ?? 1;
    }

    /**
     * Список карточек, которые используются в разделах.
     *
     * @param array|int $sections Раздел или список разделов
     *
     * @return array
     */
    public static function cardList($sections)
    {
        $query = Query::SelectFrom(self::$sTableName)
            ->fields('DISTINCT ext_card_id AS card', true)
            ->join('left', GoodsTable::getTableName(), 'jt', 'goods_id=base_id')
            ->where('section_id', $sections)
            ->asArray();

        $out = [];
        while ($row = $query->each()) {
            if ($row['card']) {
                $out[] = (int) $row['card'];
            }
        }

        return $out;
    }

    /**
     * @param $iGoodId
     * @param $iBaseCard
     *
     * @return bool|string
     */
    public static function get4Goods($iGoodId, $iBaseCard)
    {
        $key = $iGoodId . '_' . $iBaseCard;

        if (self::$updateCache || !isset(self::$cache[$key])) {
            $query = Query::SelectFrom(self::$sTableName)
                ->where('goods_id', $iGoodId)
                ->where('goods_card', $iBaseCard)
                ->asArray();

            $aSectionList = [];
            while ($aItem = $query->each()) {
                $aSectionList[] = $aItem['section_id'];
            }

            self::setCache($key, $aSectionList);
            self::setUpdateCache(0);
        }

        return count(self::getCache($key)) ? self::getCache($key) : [];
    }

    /**
     * Список товаров для раздела.
     *
     * @param int $iSectionId
     *
     * @return array|bool
     */
    public static function getGoodsList($iSectionId)
    {
        $query = Query::SelectFrom(self::$sTableName)
            ->where('section_id', $iSectionId)
            ->order('priority')
            ->asArray();

        $aGoodsList = [];
        while ($aItem = $query->each()) {
            $aGoodsList[] = (int) $aItem['goods_id'];
        }

        return count($aGoodsList) ? $aGoodsList : false;
    }

    /**
     * Устанавливает товарную позицию первой в разделе.
     *
     * @param $iSectionId
     * @param $iItemId
     *
     * @return bool
     */
    public static function sortUp($iSectionId, $iItemId)
    {
        $oItem = Query::SelectFrom(self::$sTableName)
            ->where('section_id', (int) $iSectionId)
            ->order('priority')
            ->getOne();

        if (!$oItem) {
            return false;
        }

        return self::sortSwap($iSectionId, $iItemId, $oItem['goods_id'], 'before');
    }

    /**
     * Соортировка товарных позиций внутри раздела.
     *
     * @param $iSectionId
     * @param $iItemId
     * @param $iPlaceId
     * @param string $sPos
     *
     * @return bool
     */
    public static function sortSwap($iSectionId, $iItemId, $iPlaceId, $sPos = '')
    {
        $oItem = Query::SelectFrom(self::$sTableName)
            ->where('goods_id', $iItemId)
            ->where('section_id', (int) $iSectionId)
            ->getOne();

        $oTarget = Query::SelectFrom(self::$sTableName)
            ->where('goods_id', $iPlaceId)
            ->where('section_id', (int) $iSectionId)
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
            self::shiftPosition($iSectionId, $iStartPos, $iEndPos, '+');
            self::changePosition($iItemId, $iSectionId, $iNewPos);
        } else {
            $iStartPos = $iItemPos;
            $iEndPos = $sPos == 'after' ? $iTargetPos + 1 : $iTargetPos;
            $iNewPos = $sPos == 'after' ? $iTargetPos : $iTargetPos - 1;
            self::shiftPosition($iSectionId, $iStartPos, $iEndPos, '-');
            self::changePosition($iItemId, $iSectionId, $iNewPos);
        }

        $oEntityRow = Entity::get(Card::DEF_BASE_CARD);
        GoodsTable::setChangeDate($iItemId, $oEntityRow->id);
        GoodsTable::setChangeDate($iPlaceId, $oEntityRow->id);

        return true;
    }

    private static function shiftPosition($iSection, $iStartPos, $iEndPos, $sSign = '+')
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('priority=priority' . $sSign . '?', 1)
            ->where('priority>?', (int) $iStartPos)
            ->where('priority<?', (int) $iEndPos)
            ->where('section_id', (int) $iSection)
            ->get();
    }

    private static function changePosition($iGoods, $iSection, $iPos)
    {
        Query::UpdateFrom(self::$sTableName)
            ->set('priority', (int) $iPos)
            ->where('goods_id', (int) $iGoods)
            ->where('section_id', (int) $iSection)
            ->get();
    }

    /**
     * @param $iGoods
     * @param $iSection
     *
     * @throws \yii\db\Exception
     *
     * @return null|mixed
     */
    public static function getNext($iGoods, $iSection)
    {
        $table = self::$sTableName;
        $base = 'co_' . Card::DEF_BASE_CARD;

        $sQuery = "SELECT * FROM `{$table}`
                    INNER JOIN {$base} ON {$base}.id = goods_id AND active
                    INNER JOIN c_goods ON base_id = goods_id AND parent = base_id
                    WHERE section_id=:section1 AND `{$table}`.priority > (SELECT priority FROM `{$table}` WHERE goods_id=:goods and section_id=:section2 LIMIT 1 )
                    ORDER BY `{$table}`.priority ASC
                    LIMIT 1
                    ";

        $query = Query::SQL(
            $sQuery,
            [
                'section1' => $iSection,
                'section2' => $iSection,
                'goods' => $iGoods,
            ]
        );

        if (!($row = $query->fetchArray())) {
            $sQuery = "SELECT * FROM `{$table}`
                    INNER JOIN {$base} ON {$base}.id = goods_id AND active
                    INNER JOIN c_goods ON base_id = goods_id AND parent = base_id
                    WHERE section_id=:section
                    ORDER BY `{$table}`.priority ASC
                    LIMIT 1
                    ";

            $query = Query::SQL($sQuery, ['section' => $iSection]);

            $row = $query->fetchArray();
        }

        return $row;
    }

    /**
     * @param $iGoods
     * @param $iSection
     *
     * @throws \yii\db\Exception
     *
     * @return null|mixed
     */
    public static function getPrev($iGoods, $iSection)
    {
        $table = self::$sTableName;
        $base = 'co_' . Card::DEF_BASE_CARD;

        $sQuery = "SELECT * FROM `{$table}`
                    INNER JOIN {$base} ON {$base}.id = goods_id AND active
                    INNER JOIN c_goods ON base_id = goods_id AND parent = base_id
                    WHERE section_id=:section1 AND `{$table}`.priority < (SELECT priority FROM `{$table}` WHERE goods_id=:goods and section_id=:section2 LIMIT 1 )
                    ORDER BY `{$table}`.priority DESC
                    LIMIT 1
                    ";

        $query = Query::SQL(
            $sQuery,
            [
                'section1' => $iSection,
                'section2' => $iSection,
                'goods' => $iGoods,
            ]
        );

        if (!($row = $query->fetchArray())) {
            $sQuery = "SELECT * FROM `{$table}`
                    INNER JOIN {$base} ON {$base}.id = goods_id AND active
                    INNER JOIN c_goods ON base_id = goods_id AND parent = base_id
                    WHERE section_id=:section
                    ORDER BY `{$table}`.priority DESC
                    LIMIT 1
                    ";

            $query = Query::SQL($sQuery, ['section' => $iSection]);

            $row = $query->fetchArray();
        }

        return $row;
    }
}
