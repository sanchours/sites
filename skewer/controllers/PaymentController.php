<?php

namespace skewer\controllers;

use skewer\base\log\Logger;
use skewer\base\site\Type;
use skewer\base\site_module\Request;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Adm\Order\Service;
use skewer\build\Tool\Payments;

class PaymentController extends Prototype
{
    public function actionIndex()
    {
        if (!Type::isShop()) {
            return 'FAIL';
        }

        //Получаем данные
        $oPayment = Payments\Api::make($this->getType());

        if (!$oPayment) {
            return 'FAIL';
        }

        try {
            $mResult = $oPayment->checkResult();
            if ($mResult === true) {
                //Проверим вначале на существование заказа и его статус
                if (!Service::checkStatus($oPayment->getOrderId(), Status::getIdByPaid())) {
                    return $oPayment->getFail();
                }

                //Меняем статус заказа, рассылаем письма и пр.
                if (Service::changeStatus($oPayment->getOrderId(), Status::getIdByPaid(), $oPayment->getSum())) {
                    $sResult = $oPayment->getSuccess();
                } else {
                    $sResult = $oPayment->getFail();
                }

                Service::sendMailChangeOrderStatus($oPayment->getOrderId(), Status::getIdByNew(), Status::getIdByPaid());
            } elseif ($mResult === false) {
                Service::changeStatus($oPayment->getOrderId(), Status::getIdByFail(), $oPayment->getSum());
                Service::sendMailChangeOrderStatus($oPayment->getOrderId(), Status::getIdByNew(), Status::getIdByFail());
                $sResult = $oPayment->getFail();
            } else {
                $sResult = $oPayment->getFail();
            }
        } catch (\Exception $e) {
            Logger::dumpException($e);
            $sResult = 'FAIL';
        }

        $oPayment->afterPayment($sResult);

        return $sResult;
    }

    /**
     * Получение типа агрегатора по get параметрам
     *
     * @return string
     */
    private function getType()
    {
        $sType = Request::getStr('MNT_TYPE');

        if (!$sType) {
            $sType = Request::getStr('shp_type');
        }

        if (!$sType) {
            $txn_id = Request::getStr('txn_id');
            if ($txn_id) {
                $sType = 'paypal';
            }
        }

        if (!$sType) {
            if (Request::getStr('ukassaPayment') == 1) {
                $sType = Payments\UkassaPayment::PAYMENT_TYPE;
            }
        }

        if (!$sType) {
            if (Request::getStr('tinkoffPayment') == 1 and isset($_SERVER['REMOTE_ADDR'])) {
                $remote_addr = explode('.', $_SERVER['REMOTE_ADDR']);
                $keyEnd = array_keys($remote_addr); //Решил разбить, то работет, то нет.
                $keyEnd = end($keyEnd);
                unset($remote_addr[$keyEnd]);
                $remote_addr = implode('.', $remote_addr);
                //		        if ($remote_addr == Payments\TinkoffPayment::GATEWAY_IP) {
                $sType = 'tinkoff';
            //		        }
            } elseif (Request::getStr('testPayment') == '1') {
                $sType = 'test';
            }
        }

        return $sType;
    }
}
