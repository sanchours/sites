<?php

namespace skewer\build\Tool\Payments\ar;

use skewer\base\ft;
use skewer\base\orm;

/**
 * Class Params.
 */
class PayPalPayments extends orm\TablePrototype
{
    protected static $sTableName = 'paypal_payments';

    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get('paypal_payments')
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('order_id', 'int(11)', 'order_id')
            ->addField('payment', 'varchar(64)', 'payment')
            ->addField('href', 'varchar(256)', 'href')
            ->addField('date', 'datetime', 'date')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new PayPalPaymentRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
