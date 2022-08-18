<?php

namespace skewer\build\Tool\Payments;

use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PaylibPayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception as PayPalException;
use PayPal\Rest\ApiContext;
use skewer\base\log\Logger;
use skewer\base\site_module\Request;
use skewer\base\SysVar;
use skewer\build\Adm\Order;
use skewer\build\Adm\Order\Api as ApiOrder;

/**
 * Class PaypalPayment.
 */
class PaypalPayment extends Payment
{
    /**
     * @var null|ApiContext
     */
    private $oApiContext;

    /**
     * @var null|Transaction
     */
    private $oTransaction;

    /**
     * @var null|Amount
     */
    private $oAmount;

    /**
     * @var null|ItemList
     */
    private $oItemList;

    /**
     * Код валюты.
     *
     * @var string
     */
    private $sCurrency = 'RUB';

    /**
     * @var string
     */
    private $paymentId = '';

    /**
     * clientId.
     *
     * @var string
     */
    private $clientId = '';

    /**
     * clientSecret.
     *
     * @var string
     */
    private $clientSecret = '';

    /**
     * Сообщение об успешной оплате.
     *
     * @var string
     */
    private $sSuccessMsg = '';

    /**
     * @var array Список полей для редактирования
     */
    protected static $aFields = [
        ['clientId', 'PayPal_clientId_field', 's', 'str'],
        ['clientSecret', 'PayPal_clientSecret_field', 's', 'pass'],
        ['currency', 'PayPal_currency_field', 's', 'str'],
    ];

    public function init()
    {
        $this->oApiContext = false;

        $this->sCurrency = SysVar::get('PayPal.currency');

        $this->oAmount = new Amount();
        $this->oAmount->setCurrency($this->sCurrency);

        $this->oTransaction = new Transaction();

        $this->oItemList = new ItemList();
    }

    private function getContext()
    {
        try {
            if (!$this->clientId || !$this->clientSecret) {
                throw new \Exception('not auth!');
            }

            $oApiContext = new ApiContext(new OAuthTokenCredential(
                $this->clientId,
                $this->clientSecret
            ));

            if ($this->test_life) {
                $oApiContext->setConfig(['mode' => 'sandbox']);
            } else {
                $oApiContext->setConfig(['mode' => 'live']);
            }
        } catch (\Exception $e) {
            Logger::dump($e);
            $oApiContext = false;
        }

        return $oApiContext;
    }

    /**
     * Тип агрегатора систем оплат
     *
     * @return mixed
     */
    public function getType()
    {
        return 'PayPal';
    }

    /**
     * Устанавливаем список товаров в заказе.
     */
    private function setItems()
    {
        $aItems = Order\ar\Goods::find()->where('id_order', $this->orderId)->asArray()->getAll();
        if (count($aItems)) {
            $aItemsOrder = [];
            foreach ($aItems as $aItem) {
                if (!is_array($aItem)) {
                    continue;
                }
                $oItem = new Item();
                $oItem->setName((isset($aItem['title'])) ? $aItem['title'] : '');
                $oItem->setCurrency($this->sCurrency);
                $oItem->setPrice((isset($aItem['price'])) ? $aItem['price'] : '');
                $oItem->setQuantity((isset($aItem['count'])) ? $aItem['count'] : '');
                $oItem->setSku((isset($aItem['id_goods'])) ? $aItem['id_goods'] : '');

                $aItemsOrder[] = $oItem;
            }
            if (count($aItemsOrder)) {
                $this->oItemList->setItems($aItemsOrder);
            }
        }
    }

    /** {@inheritdoc} */
    public function checkResult()
    {
        /*        if (!$this->IPNValidate()){
                    $this->sSuccessMsg = 'fail';
                }*/

        $payment_status = Request::getStr('payment_status');
        $txn_id = Request::getStr('txn_id');
        $mc_gross = Request::getStr('mc_gross');
        // $_GET['txn_id']          Ид платежа PayPal
        // $_GET['mc_gross']        Сумма платежа
        // $_GET['mc_currency']     Валюта платежа
        // $_GET['payer_email']     Еmail плательщика
        // $_GET['item_number1']    Ид первого товара
        // $_GET['payment_status']  Статус заказа
        // $_GET['receiver_email']  Email получателя

        $payment_status = mb_strtolower($payment_status);

        switch ($payment_status) {
            // Платеж успешно выполнен, оказываем услугу
            case 'completed':
                /**
                 * @var ar\PayPalPaymentRow
                 */
                $oPay = ar\PayPalPayments::find()->where('payment', $txn_id)->getOne();

                if (!$oPay) {
                    return false;
                }

                if ($oPay->order_id) {
                    $this->orderId = $oPay->order_id;

                    /**
                     * проверим сумму.
                     */
                    $price = ApiOrder::getOrderSum($oPay->order_id);

                    $this->setSum($price);

                    if ($mc_gross == $price) {
                        $oPay->delete();

                        return true;
                    }
                }
                break;
            // Платеж не прошел
            case 'failed':
                break;
            // Платеж отменен продавцом
            case 'denied':
                break;
            // Деньги были возвращены покупателю
            case 'refunded':
                break;
        }

        $this->sSuccessMsg = $payment_status;

        return false;
    }

    /**
     * Проверка валидации в IPN.
     *
     * @return bool;
     */
    /*    public function IPNValidate(){
            if (defined('PayPalSandbox') && PayPalSandbox == 1){
                $ipn = new IPN\PPIPNMessage(null, array('mode' => 'sandbox'));
            }else{
                $ipn = new IPN\PPIPNMessage(null, array('mode' => 'live'));
            }

            return $ipn->validate();
        }*/

    /**
     * Вывод формы для оплаты.
     *
     * @return string
     */
    public function getForm()
    {
        /**
         * @var ar\PayPalPaymentRow
         */
        $oPayment = ar\PayPalPayments::find()->where('order_id', $this->orderId)->getOne();

        $sRes = '';

        if (!$oPayment || !$oPayment->href) {
            /** Если первый раз - создадим оплату в пайпале */
            $oPayer = new Payer();
            $oPayer->setPaymentMethod('paypal');

            $payment = new PaylibPayment();
            $payment->setIntent('sale');
            $payment->setPayer($oPayer);

            $total = number_format($this->sum, 2, '.', '');
            $this->oAmount->setTotal($total);

            $this->oTransaction->setAmount($this->oAmount);
            $this->oTransaction->setDescription($this->description);

            $this->setItems();

            $this->oTransaction->setItemList($this->oItemList);
            $payment->setTransactions([$this->oTransaction]);

            $oRedirect = new RedirectUrls();
            $oRedirect->setCancelUrl($this->getSuccessUrl());
            $oRedirect->setReturnUrl($this->getSuccessUrl());

            $payment->setRedirectUrls($oRedirect);

            try {
                $oContext = $this->getContext();
                if (!$oContext) {
                    return $sRes;
                }
                $payment->create($this->getContext());
            } catch (PayPalException\PayPalConnectionException $e) {
                Logger::dump(json_decode($e->getData()));

                return $sRes;
            }

            if ($payment->getState() == 'created') {
                $oPayment = ar\PayPalPayments::getNewRow();

                $oPayment->order_id = $this->orderId;
                $oPayment->payment = $payment->getId();
                $oPayment->date = date('Y-m-d H:i:s');
                $this->paymentId = $payment->getId();

                if ($payment->getState() == 'created') {
                    $links = $payment->getLinks();
                    foreach ($links as $link) {
                        if ($link->getMethod() == 'REDIRECT') {
                            $oForm = new Form();
                            $sHref = $link->getHref();
                            $oForm->setAction($sHref);
                            $oPayment->href = $sHref;

                            return $this->parseForm($oForm);
                        }
                    }
                }
                $oPayment->save();
            }
        } else {
            $oForm = new Form();
            $oForm->setAction($oPayment->href);

            return $this->parseForm($oForm);
        }

        return $sRes;
    }

    /**
     * Выполнение платежа.
     *
     * @return bool
     */
    public function execute()
    {
        /**
         * @var ar\PayPalPaymentRow
         */
        $oPay = ar\PayPalPayments::find()->where('order_id', $this->orderId)->getOne();

        if (!$oPay) {
            return false;
        }

        $paymentId = $oPay->payment;

        $oPayment = PaylibPayment::get($paymentId, $this->oApiContext);

        $iPayerId = Request::getStr('PayerID');

        if ($iPayerId == '') {
            return false;
        }
        $oPaymentExecution = new PaymentExecution();
        $oPaymentExecution->setPayerId($iPayerId);

        try {
            $oPayment->execute($oPaymentExecution, $this->getContext());

            return true;
        } catch (PayPalException\PayPalConnectionException $e) {
            Logger::dump(json_decode($e->getData()));

            return false;
        }
    }

    /**
     * Сообщение об успешной оплате.
     *
     * @return string
     */
    public function getSuccess()
    {
        return $this->sSuccessMsg;
    }

    /**
     * Сообщение о неуспешной оплате.
     *
     * @return string
     */
    public function getFail()
    {
        return 'fail';
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
                    case 'test_life':
                        $this->test_life = $aParam;
                        break;
                    case 'clientId':
                        $this->clientId = $aParam;
                        break;
                    case 'clientSecret':
                        $this->clientSecret = $aParam;
                        break;
                    case 'currency':
                        $this->sCurrency = $aParam;
                        $this->oAmount->setCurrency($this->sCurrency);
                        break;
                }
            }
            $this->bInitParams = true;
        }
    }
}
