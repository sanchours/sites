<?php

namespace skewer\build\Tool\Payments\ar;

use skewer\base\orm;

/**
 * Class Params.
 */
class SberbankPaymentRow extends orm\ActiveRecord
{
    public $id = 0;
    public $order_id = 0;
    public $invoice = '';
    public $description = '';
    public $amount = 0;
    public $error_code = '';
    public $error_message = '';
    public $num_sberbank = '';
    public $url_sberbank = '';
    public $add_date = '';

    public function __construct()
    {
        $this->setTableName('sberbank_payments');
        $this->setPrimaryKey('id');
    }
}
