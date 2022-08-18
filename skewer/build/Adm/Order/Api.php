<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 11.05.2016
 * Time: 16:59.
 */

namespace skewer\build\Adm\Order;

use skewer\base\SysVar;
use skewer\build\Adm\Order\ar\Order;
use skewer\build\Tool\DeliveryPayment\Api as DeliveryApi;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;
use skewer\components\modifications\GetModificationEvent;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class Api
{
    public static function getLastModification()
    {
        $aRow = Order::find()
            ->order('id', 'DESC')
            ->asArray()
            ->getOne();

        return (isset($aRow['date'])) ? strtotime($aRow['date']) : 0;
    }

    public static function className()
    {
        return 'skewer\build\Adm\Order\Api';
    }

    public static function getLastMod(GetModificationEvent $event)
    {
        $event->setLastTime(self::getLastModification());
    }

    /**
     * Получить информацию о заказе/заказах: число позиций, общую стоимость.
     *
     * @param array|int $mOrderId
     *
     * @return array Проиндексированный по id_order массив с полями id_order, count, sum
     */
    public static function getGoodsStatistic($mOrderId)
    {
        if (!$mOrderId) {
            return [];
        }

        return ar\Goods::find()
            ->fields('id_order, COUNT(`id`) AS count, SUM(`total`) AS sum')
            ->where('id_order', $mOrderId)
            ->groupBy('id_order')
            ->index('id_order')
            ->asArray()
            ->getAll();
    }

    /**
     * Получить общую стоимость позиций заказа.
     *
     * @param int $iOrderId
     * @param bool $bWithSumDelivery - включать стоимость доставки?
     *
     * @return int
     */
    public static function getOrderSum($iOrderId, $bWithSumDelivery = false)
    {
        $aGoodsStatistic = self::getGoodsStatistic($iOrderId);

        $sSum = 0;

        if ($aGoodsStatistic) {
            $sSum = $aGoodsStatistic[$iOrderId]['sum'];
        }

        if ($bWithSumDelivery) {
            $aOrder = Order::find()
                ->where('id', $iOrderId)
                ->asArray()
                ->getOne();

            $sSum += $aOrder['price_delivery'];
        }

        return $sSum;
    }

    public static function getJsonCacheCart(\skewer\build\Page\Cart\Order $oOrderCart)
    {
        return json_encode([
            'paid_delivery' => SysVar::get(DeliveryApi::PAID_DELIVERY),
            'free_shipping' => $oOrderCart->getFreeShipping(),
            'coord_deliv_costs' => $oOrderCart->getCoordDelivCosts(),
            'hide_price_fractional' => SysVar::get('catalog.hide_price_fractional'),
        ]);
    }

    public static function getArrayCacheCart($sData, $priceDelivery)
    {
        $aData = json_decode($sData, true);

        $aData['price_delivery'] = $priceDelivery;
        $aData['currency'] = true;

        if ((isset($aData['free_shipping']) && $aData['free_shipping']) ||
                (isset($aData['coord_deliv_costs']) && $aData['coord_deliv_costs'])) {
            $aData['currency'] = false;
        }

        if (isset($aData['free_shipping']) && $aData['free_shipping']) {
            $aData['price_delivery'] = \Yii::t('order', 'freeShipping');
        }
        if (isset($aData['coord_deliv_costs']) && $aData['coord_deliv_costs']) {
            $aData['price_delivery'] = \Yii::t('order', 'coordDelivCosts');
        }

        return $aData;
    }

    /**
     * @param null $iTypeDelivery
     *
     * @return array
     */
    public static function getPaymentList($iTypeDelivery = null)
    {
        $aTypePaymentList = TypePayment::find()->orderBy('priority')->all();
        if (empty($aTypePaymentList)) {
            return [];
        }

        $aPaymentDelivery = [];
        if ($iTypeDelivery !== null) {
            $aPaymentDelivery = (new Query())
                ->from('orders_delivery_payment')
                ->select(['payment_id'])
                ->where(['delivery_id' => $iTypeDelivery])
                ->all();

            $aPaymentDelivery = ArrayHelper::getColumn($aPaymentDelivery, 'payment_id');
        }

        foreach ($aTypePaymentList as $item) {
            $aPaymentList[$item->id] = $item->title;
            if (!$item['active']) {
                $aPaymentList[$item->id] = $item->title . \Yii::t('order', 'not_active');
            } else {
                if ($aPaymentDelivery) {
                    if (array_search($item->id, $aPaymentDelivery) === false) {
                        $aPaymentList[$item->id] = $item->title . \Yii::t('order', 'not_active');
                    }
                }
            }
        }

        return $aPaymentList;
    }

    /**
     * @return array
     */
    public static function getDeliveryList()
    {
        $aTypeDeliveryList = TypeDelivery::find()->orderBy('priority')->asArray()->all();

        if (empty($aTypeDeliveryList)) {
            return [];
        }

        foreach ($aTypeDeliveryList as $item) {
            if (!$item['active']) {
                $aDeliveryList[$item['id']] = $item['title'] . \Yii::t('order', 'not_active');
            } else {
                $aDeliveryList[$item['id']] = $item['title'];
            }
        }

        return $aDeliveryList;
    }
}
