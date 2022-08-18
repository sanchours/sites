<?php
/**
 * User: Max
 * Date: 25.07.14.
 */

namespace skewer\build\Adm\Order\ar;

use skewer\base\orm;

class GoodsRow extends orm\ActiveRecord
{
    public $id = 0;
    public $title = '';
    public $count = 0;
    public $total = 0;
    public $price = 0;
    public $payment_object = '';
    public $id_goods = 0;
    public $id_order = 0;

    public function __construct()
    {
        $this->setTableName('orders_goods');
        $this->setPrimaryKey('id');
    }
}
