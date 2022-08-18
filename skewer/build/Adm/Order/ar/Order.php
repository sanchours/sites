<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 23.04.14
 * Time: 15:54.
 */

namespace skewer\build\Adm\Order\ar;

use skewer\base\ft;
use skewer\base\orm;
use skewer\build\Tool\Payments\UkassaPayment;

class Order extends orm\TablePrototype
{
    protected static $sTableName = 'orders';
    protected static $sKeyField = 'id';

    /**
     * Объявление сущности.
     */
    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('date', 'datetime', 'field_date')
            ->addField('address', 'varchar(255)', 'field_address')
            ->addField('person', 'varchar(255)', 'field_contact_face')
            ->addField('phone', 'varchar(255)', 'field_phone')
            ->addField('mail', 'varchar(255)', 'field_mail')
            ->addField('postcode', 'varchar(255)', 'field_postcode')

            ->addField('status', 'int(11)', 'field_status')
            ->addField('price_delivery', 'int(11)', 'field_price_delivery')

            ->addField('type_payment', 'int(11)', 'field_payment')
            ->addField('type_delivery', 'int(11)', 'field_delivery')

            ->addField('text', 'text', 'field_text')
            ->addField('token', 'varchar(255)', 'field_token')
            ->addField('notes', 'text', 'field_notes')
            ->addField('auth', 'int(11)', 'field_user_id')
            ->addField('is_mobile', 'int(1)', 'Mobile')
            ->addField('paymentId', 'int(21)', 'paymentId')
            ->addField('cache_cart', 'text', 'cache_cart')
            ->addField(UkassaPayment::ORDER_FIELD, 'varchar(255)', 'field_ukassa_payment_id')

            ->addDefaultProcessorSet()
            ->addColumnSet(
                'list',
                ['id', 'date', 'person', 'mail', 'status']
            )
            ->addColumnSet(
                'edit',
                ['id', 'date', 'person', 'postcode', 'address', 'phone', 'mail', 'status', 'type_payment', 'type_delivery', 'text', 'notes']
            )
            ->addColumnSet(
                'mail',
                ['person', 'postcode', 'address', 'phone', 'mail', 'type_payment', 'type_delivery', 'text']
            )
            ->save()
            //->build()
;
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new OrderRow();
        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
