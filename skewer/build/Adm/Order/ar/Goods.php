<?php
/**
 * User: Max
 * Date: 25.07.14.
 */

namespace skewer\build\Adm\Order\ar;

use skewer\base\ft;
use skewer\base\orm;

class Goods extends orm\TablePrototype
{
    protected static $sTableName = 'orders_goods';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)

            ->addField('title', 'varchar(255)', 'order.field_goods_title')

            ->addField('count', 'int(11)', 'order.field_goods_count')
            ->addField('total', 'decimal(12,2)', 'order.field_goods_total')
            ->addField('price', 'decimal(12,2)', 'order.field_goods_price')
            ->addField('payment_object', 'varchar(255)', 'order.field_goods_payment_object')

            ->addField('id_order', 'int(11)', 'order.field_goods_id_order')
            ->addField('id_goods', 'int(11)', 'order.field_goods_id')

            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new GoodsRow();
        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    /**
     * Получить товары заказа по id заказа.
     *
     * @param int $iOrderId - ид заказа
     * @param bool $bAsArray - в виде массива?
     *
     * @return mixed
     */
    public static function getByOrderId($iOrderId, $bAsArray = true)
    {
        $oQuery = self::find()
            ->where('id_order', $iOrderId);

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->getAll();
    }
}
