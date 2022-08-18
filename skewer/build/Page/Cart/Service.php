<?php

namespace skewer\build\Page\Cart;

use skewer\base\site\ServicePrototype;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use yii\helpers\ArrayHelper;

/**
 * Class Service
 * Удаленный запуск функция.
 */
class Service extends ServicePrototype
{
    /**
     * Получение всех типов доставки.
     *
     * @return array
     */
    public function getTypeDelivery()
    {
        $aTypeDelivery = TypeDelivery::find()->where(['active' => 1])->orderBy('priority')->asArray()->all();

        return ArrayHelper::map($aTypeDelivery, 'id', 'title');
    }

    /**
     * Получение всех типов оплаты.
     *
     * @return array
     */
    public function getTypePayment()
    {
        /** @var TypeDelivery $oTypeDelivery */
        $oTypeDelivery = TypeDelivery::find()
            ->orderBy('priority')
            ->one();

        return $oTypeDelivery->getDeliveryPaymentAsArray();
    }
}
