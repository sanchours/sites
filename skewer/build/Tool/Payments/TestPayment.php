<?php

namespace skewer\build\Tool\Payments;

use skewer\base\site_module\Request;
use skewer\build\Adm\Order\Api as ApiOrder;

class TestPayment extends Payment
{
    /** @var bool Активность */
    protected $active = false;

    /** @var string URL для отправки формы */
    private $url = '/payment.php?';

    public function getType()
    {
        return 'test';
    }

    /**
     * Конструктор
     */
    public function init()
    {
    }

    /**
     * Построение формы.
     *
     * @return string
     */
    public function getForm()
    {
        if (!$this->active) {
            return '';
        }

        $this->setDescription(\Yii::t('order', 'order_description', [(int) $this->getOrderId()]));

        $aParams = [
            'testPayment' => 1,
            'InvId' => $this->orderId,
            'OutSum' => $this->sum,
            'SignatureValue' => $this->createResultSignature($this->sum, $this->orderId),
        ];

        $oForm = new Form();
        $oForm->setAction($this->url . http_build_query($aParams));

        $aItems = [];
        $aItems['OutSum'] = $this->sum;
        $aItems['InvId'] = $this->orderId;
        $aItems['Desc'] = $this->description;
        $aItems['SignatureValue'] = $this->createResultSignature($this->sum, $this->orderId);
        $aItems['testPayment'] = true;

        $oForm->setFields($aItems);

        return $this->parseForm($oForm);
    }

    /**
     * Генерирует подпись для сравнения после оплаты.
     *
     * @param float $sum Сумма заказа
     * @param int $orderId Идентификатор заказа
     *
     * @return string
     */
    public function createResultSignature($sum, $orderId)
    {
        return md5($sum . ':' . $orderId);
    }

    /** {@inheritdoc} */
    public function checkResult()
    {
        $sum = Request::getStr('OutSum');
        $orderId = (int) Request::getStr('InvId');
        $signature = Request::getStr('SignatureValue');

        if ($sum != ApiOrder::getOrderSum($orderId)) {
            return false;
        }

        $this->setOrderId($orderId);
        $this->setSum($sum);

        return mb_strtolower($signature) == $this->createResultSignature($sum, $orderId);
    }

    /**
     * Сообщение об успешной оплате.
     *
     * @return string
     */
    public function getSuccess()
    {
        return 'SUCCESS';
    }

    /**
     * Сообщение о неуспешной оплате.
     *
     * @return string
     */
    public function getFail()
    {
        return 'FAIL';
    }

    /**
     * Инициализация параметров.
     *
     * @param array $aParams
     */
    public function initParams($aParams = [])
    {
        if ($aParams) {
            foreach ($aParams as $sKey => $aParam) {
                switch ($sKey) {
                    case 'active':
                        $this->active = $aParam;
                        break;
                }
            }
            $this->bInitParams = true;
        }
    }

    public function afterPayment($sResult)
    {
        if ($sResult == $this->getSuccess()) {
            \Yii::$app->response->redirect($this->getSuccessUrl());
        } else {
            \Yii::$app->response->redirect($this->getCancelUrl());
        }
    }
}
