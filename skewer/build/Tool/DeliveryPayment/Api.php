<?php

namespace skewer\build\Tool\DeliveryPayment;

use skewer\base\SysVar;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\ext\ListRows;

/**
 *  Class Api.
 */
class Api
{
    const FIELD_SORT = 'priority';
    const PAID_DELIVERY = 'delivery_payment.paid_delivery';

    /**
     * @param $sNameModel
     *
     * @throws \yii\db\Exception
     *
     * @return int
     */
    public static function getMaxPriority($sNameModel)
    {
        $aLastPriority = \Yii::$app->getDb()->createCommand(
            '
            SELECT MAX(`' . self::FIELD_SORT . '`)
            FROM ' . $sNameModel
            )->query()->read();

        $aLastPriority = (int) reset($aLastPriority) + 1;

        return $aLastPriority;
    }

    /**
     * @param $oItem
     * @param $iItemId
     * @param $iItemTargetId
     * @param $sPosition
     *
     * @return bool
     */
    public static function sortItems($oItem, $iItemId, $iItemTargetId, $sPosition)
    {
        return \skewer\base\ui\Api::sortObjects($iItemId, $iItemTargetId, $oItem, $sPosition, '', 'id', self::FIELD_SORT);
    }

    public static function updFastList(ActiveRecord $oItem, Module $oModule)
    {
        $oListVals = new ListRows();
        $oListVals->setSearchField(['id']);
        $oListVals->addDataRow($oItem->getAttributes());
        $oListVals->setData($oModule);
    }

    public static function getEditFieldsTypeDdelivery()
    {
        $aEditFields = ['active'];

        if (SysVar::get(self::PAID_DELIVERY)) {
            $aEditFields = array_merge($aEditFields, ['price', 'coord_deliv_costs', 'free_shipping']);
        }

        return $aEditFields;
    }

    public static function getTitleTypePayment($id)
    {
        $oTypePayment = models\TypePayment::findOne(['id' => $id]);
        if ($oTypePayment) {
            return $oTypePayment->title;
        }

        return '';
    }

    public static function getTitleTypeDelivery($id)
    {
        $oTypeDelivery = models\TypeDelivery::findOne(['id' => $id]);
        if ($oTypeDelivery) {
            return $oTypeDelivery->title;
        }

        return '';
    }

    /**
     * Доставка платная?
     *
     * @return bool
     */
    public static function isDeliveryPaid()
    {
        return (bool) SysVar::get(self::PAID_DELIVERY, false);
    }
}
