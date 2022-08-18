<?php

namespace skewer\build\Tool\Payments\ar;

use skewer\base\orm;

/**
 * Class Params.
 */
class PayPalPaymentRow extends orm\ActiveRecord
{
    public $id = 0;
    public $order_id = 0;
    public $payment = '';
    public $href = '';
    public $date = '';

    public function __construct()
    {
        $this->setTableName('paypal_payments');
        $this->setPrimaryKey('id');
    }
}
