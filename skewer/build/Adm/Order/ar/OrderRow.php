<?php

namespace skewer\build\Adm\Order\ar;

use skewer\base\orm;
use skewer\build\Tool\DeliveryPayment\Api;

class OrderRow extends orm\ActiveRecord
{
    public $id = 0;
    public $date = '';
    public $address = '';
    public $person = '';
    public $phone = '';
    public $mail = '';
    public $status = '';
    public $postcode = '';
    public $price_delivery = '';
    public $type_payment = 0;
    public $type_delivery = 0;
    public $text = '';
    public $notes = '';
    public $token = '';
    public $auth = '';
    public $is_mobile = 0;
    public $paymentId = 0;
    public $cache_cart = '';

    public function __construct()
    {
        $this->setTableName('orders');
        $this->setPrimaryKey('id');
    }

    public function initSave()
    {
        if (!$this->date || $this->date === 'null') {
            $this->date = date('Y-m-d H:i:s', time());
        }

        return parent::initSave();
    }

    public function delete()
    {
        /*
         * надо удалить и товары, привязанные к заказу
         */
        if ($this->id) {
            $aGoods = Goods::find()->where('id_order', $this->id)->getAll();
            if ($aGoods) {
                /*
                 * @var GoodsRow
                 */
                foreach ($aGoods as $oGoodsRow) {
                    Goods::delete($oGoodsRow->id);
                }
            }
        } else {
            Goods::delete();
        }

        parent::delete();
    }

    /**
     * Получить данные заказа в формате
     * [
     *      ['title' => 'Заголовок поля1', 'value' => 'Значение поля1'],
     *      ['title' => 'Заголовок поля2', 'value' => 'Значение поля2']
     * ].
     *
     * @param array $aAllowFields - включаемые в выходной массив поля
     *
     * @return array
     */
    public function getDataOrder($aAllowFields)
    {
        $aDataOrder = [];

        $aVars = $this->getData();

        foreach ($aAllowFields as $item) {
            // только нужные нам поля
            if (!isset($aVars[$item])) {
                continue;
            }

            $sLangTitle = \Yii::t('order', Order::getModel()->getFiled($item)->getTitle());

            switch ($item) {
                case 'type_payment':
                    $aDataOrder[] = [
                        'title' => $sLangTitle,
                        'value' => Api::getTitleTypePayment($aVars[$item]),
                    ];
                    break;

                case 'type_delivery':
                    $aDataOrder[] = [
                        'title' => $sLangTitle,
                        'value' => Api::getTitleTypeDelivery($aVars[$item]),
                    ];
                    break;

                default:
                    $aDataOrder[] = [
                        'title' => $sLangTitle, 'value' => $aVars[$item],
                    ];
            } //end switch
        } //end foreach

        return $aDataOrder;
    }
}
