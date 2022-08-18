<?php

namespace skewer\build\Tool\Payments;

use skewer\base\SysVar;
use yii\helpers\ArrayHelper;

/**
 * Class Api.
 */
class Api
{
    const PAYMENT_METHOD = 'payment_method';
    const DEFAULT_PAYMENT_METHOD = 'full_prepayment';
    const DEFAULT_PAYMENT_OBJECT = 'commodity';

    /**
     * Список оплат в системе.
     *
     * @var array
     */
    public static $aList = [
        'robokassa',
        'payanyway',
        'paypal',
        UkassaPayment::PAYMENT_TYPE,
        'test',
        'tinkoff',
        'sberbank',
    ];

    public static $aPaymentMethod = [
        'full_prepayment',
        'prepayment',
        'advance',
        'full_payment',
        'partial_payment',
        'credit',
        'credit_payment',
    ];

    public static $aPaymentObject = [
        'commodity',
        'excise',
        'job',
        'service',
        'gambling_bet',
        'gambling_prize',
        'lottery',
        'lottery_prize',
        'intellectual_activity',
        'payment',
        'agent_commission',
        'composite',
        'another',
        'property_right',
        'non-operating_gain',
        'insurance_premium',
        'sales_tax',
        'resort_fee',
    ];

    /**
     * Возвращает полный список систем оплаты.
     *
     * @param bool $bActive только активные
     *
     * @return array
     */
    public static function getPaymentsList($bActive = false)
    {
        $oQuery = ar\Params::find()->where('type IN ?', static::$aList)->where('name', 'active');

        if ($bActive) {
            $oQuery->where('value', 1);
        }
        $aItems = $oQuery->asArray()->getAll();

        if ($aItems) {
            foreach ($aItems as &$aItem) {
                $aItem['title'] = \Yii::t('payments', $aItem['type']);
            }
        }

        return $aItems;
    }

    /**
     * Возвращает экземпляр класса платежей по типу.
     *
     * @param $sPaymentType
     *
     * @return bool|Payment
     */
    public static function make($sPaymentType)
    {
        if (!static::existType($sPaymentType)) {
            return false;
        }

        $sClassName = __NAMESPACE__ . '\\' . mb_convert_case($sPaymentType, MB_CASE_TITLE) . 'Payment';

        if (!class_exists($sClassName)) {
            return false;
        }

        if (!is_subclass_of($sClassName, __NAMESPACE__ . '\\' . 'Payment')) {
            return false;
        }

        if (!method_exists($sClassName, 'getInstance')) {
            return false;
        }

        /**
         * @var Payment
         */
        $oPayment = $sClassName::getInstance();

        if (!$oPayment->isInitParams()) {
            $aParams = ar\Params::find()->where('type', $sPaymentType)->asArray()->getAll();

            $aParams = ArrayHelper::map($aParams, 'name', 'value');

            $oPayment->initParams($aParams);
        }

        if ($oPayment->getActive()) {
            return $oPayment;
        }

        return false;
    }

    /**
     * Список полей для редактирования для типа оплаты.
     *
     * @param $sPaymentType
     *
     * @return array
     */
    public static function getFields4PaymentType($sPaymentType)
    {
        if (!static::existType($sPaymentType)) {
            return [];
        }

        $sClassName = __NAMESPACE__ . '\\' . mb_convert_case($sPaymentType, MB_CASE_TITLE) . 'Payment';

        if (!class_exists($sClassName)) {
            return [];
        }

        if (!is_subclass_of($sClassName, __NAMESPACE__ . '\\' . 'Payment')) {
            return [];
        }

        if (!method_exists($sClassName, 'getFields')) {
            return [];
        }

        return $sClassName::getFields();
    }

    /**
     * Проверка типа оплаты на существование в системе.
     *
     * @param $sType
     *
     * @return bool
     */
    public static function existType($sType)
    {
        return (array_search($sType, static::$aList) !== false) ? true : false;
    }

    /**
     * Получение значений параметров для типа оплаты.
     *
     * @param $sType
     * @param array $aParamNames
     *
     * @return array|bool
     */
    public static function getParams($sType, $aParamNames = [])
    {
        $oQuery = ar\Params::find()->where('type', $sType);
        if (count($aParamNames)) {
            $oQuery->where('name IN ?', $aParamNames);
        }
        $aItems = $oQuery->asArray()->getAll();

        if ($aItems) {
            return ArrayHelper::map($aItems, 'name', 'value');
        }

        return false;
    }

    /**
     * Название типа оплаты.
     *
     * @param array $aItem
     *
     * @return string
     */
    public static function getPaymentsTitle($aItem)
    {
        return \Yii::t('Payments', $aItem['payment']);
    }

    /**
     * @return string
     */
    public static function getPaymentMethod()
    {
        return SysVar::get(self::PAYMENT_METHOD);
    }

    /**
     * @return mixed
     */
    public static function getTitlePaymentMethod()
    {
        foreach (self::$aPaymentMethod as $item) {
            $aItems[$item] = \Yii::t('payments', $item);
        }

        return $aItems;
    }

    /**
     * @return array
     */
    public static function getTitlePaymentObject()
    {
        foreach (self::$aPaymentObject as $item) {
            $aItems[$item] = \Yii::t('payments', $item);
        }

        return $aItems;
    }
}
