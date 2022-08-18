<?php

namespace skewer\build\Page\Payment;

use skewer\base\site_module;
use skewer\build\Adm\Order\ar\Order;
use skewer\build\Adm\Order\model\Status;
use yii\web\NotFoundHttpException;

class Module extends site_module\page\ModulePrototype
{
    /** @var string Тип уведомления */
    public $type = 'error';

    /** @var string шаблон сообщения онлайн оплаты */
    public $template = 'view.twig';

    public function execute()
    {
        $orderId = $this->getInt('InvId');
        if (!$orderId) {
            $orderId = $this->getInt('MNT_TRANSACTION_ID');
        }
        if (!$orderId) {
            $orderId = $this->getInt('order_id');
        }
        if (!$orderId) {
            $orderId = $this->getInt('orderNumber');
        }

        /* сбербанк онлайн */
        if (!$orderId) {
            $orderId = $this->get('orderId');
        }

        $sMessage = '';

        if ($orderId) {
            $oOrder = Order::findOne(['id' => $orderId]);
            $sStatus = $oOrder->status;
            switch($sStatus) {
                case Status::getIdByPaid():
                    $this->type = 'success';
                    $sMessage = \Yii::t('payments', 'success_text');
                    break;
                case Status::getIdByCancel():
                    $this->type = 'fail';
                    $sMessage = \Yii::t('payments', 'fail_text', $orderId);
                    break;
                case Status::getIdByNew():
                    $this->type = 'new';
                    $sMessage = \Yii::t('payments', 'new_text', $orderId);
                    break;
                default:
                    throw new NotFoundHttpException();
            }
        }

        $this->setData('message', $sMessage);
        $this->setTemplate($this->template);

        return psComplete;
    }
}
