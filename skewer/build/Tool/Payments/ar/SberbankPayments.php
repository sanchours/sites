<?php

namespace skewer\build\Tool\Payments\ar;

use skewer\base\ft;
use skewer\base\orm;

/**
 * Class Params.
 *
 * @property int order_id
 * @property string invoice
 * @property string description
 * @property int amount
 * @property int error_code
 * @property string error_message
 * @property string num_sberbank
 * @property string url_sberbank
 * @property string add_date
 */
class SberbankPayments extends orm\TablePrototype
{
    protected static $sTableName = 'sberbank_payments';

    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get('sberbank_payments')
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('order_id', 'int(11)', 'order_id')
            ->addField('invoice', 'varchar(32)', 'invoice')
            ->addField('description', 'varchar(255)', 'description')
            ->addField('amount', 'decimal(12,2)', 'amount')
            ->addField('error_code', 'varchar(3)', 'error_code')
            ->addField('error_message', 'varchar(512)', 'error_message')
            ->addField('num_sberbank', 'varchar(255)', 'num_sberbank')
            ->addField('url_sberbank', 'varchar(255)', 'url_sberbank')
            ->addField('add_date', 'datetime', 'add_date')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new SberbankPaymentRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    /**
     * Получение последней записи о заказе по идентификатору сбербанка.
     *
     * @param $sInvoiceId string Идентификатор сбербанка
     * @param mixed $bAsArray
     *
     * @return array|SberbankPaymentRow
     */
    public static function getInvoiceByIdSberbank($sInvoiceId, $bAsArray = false)
    {
        $oQuery = self::find()
            ->where('num_sberbank', $sInvoiceId);

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->order('add_date', 'DESC')->getOne();
    }

    /**
     * Получение последней записи о заказе по идентификатору с сайта.
     *
     * @param $iOrderId integer Идентификатор заказа на сайте
     * @param bool $bAsArray bool
     *
     * @return array|SberbankPaymentRow
     */
    public static function getInvoiceById($iOrderId, $bAsArray = false)
    {
        $oQuery = self::find()
            ->where('order_id', $iOrderId);

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->order('add_date', 'DESC')->getOne();
    }

    /**
     * Получение платежа по invoice сгенерированным в cms.
     *
     * @param $sInvoice string
     * @param bool $bAsArray
     *
     * @return array|SberbankPaymentRow
     */
    public static function getInvoiceByInvoice($sInvoice, $bAsArray = false)
    {
        $oQuery = self::find()
            ->where('invoice', $sInvoice);

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->getOne();
    }
}
