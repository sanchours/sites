<?php

namespace skewer\build\Tool\Payments;

use skewer\base\site_module\Parser;
use skewer\base\site_module\Request;
use skewer\build\Adm\Order\Api as ApiOrder;
use skewer\build\Adm\Order\ar\Goods as OrderGoods;
use skewer\build\Adm\Order\ar\Order;

/**
 * Class PayanywayPayment.
 */
class PayanywayPayment extends Payment
{
    /** Адреса шлюзов */
    const PRODUCTION_WSDL = 'https://www.moneta.ru/services.wsdl';
    const DEVELOPMENT_WSDL = 'https://demo.moneta.ru/services.wsdl';

    /** url */
    const PRODUCTION_URL = 'https://www.payanyway.ru/assistant.htm';
    const DEVELOPMENT_URL = 'https://demo.moneta.ru/assistant.htm';

    /** @var string url шлюза */
    private $wsdl = '';

    /** @var string url страницы */
    private $url = '';

    /** @var string Логин */
    private $sLogin = '';

    /** @var string Пароль */
    private $sPassword = '';

    /** @var string Номер счета */
    private $iInvoice = '';

    /** @var string Код проверки целостности данных */
    private $sCode = '';

    /** @var int Код ставки НДС по умолчанию НДС 0% */
    private $vat = 1104;

    /** @var string Валюта */
    private $currency = 'RUB';

    /** @var bool Дебаг */
    private $bDebug = false;

    /** @var int */
    private $subscriber = '';

    /**
     * @var array Список полей для редактирования
     */
    protected static $aFields = [
        ['login', 'Payanyway_login_field', 's', 'str'],
        ['password', 'Payanyway_password_field', 's', 'pass'],
        ['invoice', 'Payanyway_invoice_field', 'i', 'str'],
        ['code', 'Payanyway_code_field', 's', 'str'],
        ['vat', 'PayPal_VAT', 's', 'select', [1104 => 'PayPal_VAT_0', 1103 => 'PayPal_VAT_10', 1102 => 'PayPal_VAT_18', 1105 => 'PayPal_VAT_NO', 1107 => 'PayPal_VAT_10_1', 1106 => 'PayPal_VAT_18_1']],
    ];

    /**
     * Конструктор
     */
    public function init()
    {
    }

    public function getType()
    {
        return 'payanyway';
    }

    /**
     * Устанавливает логин.
     *
     * @param string $sLogin
     */
    public function setLogin($sLogin)
    {
        $this->sLogin = $sLogin;
    }

    /**
     * Возвращает модуль.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->sLogin;
    }

    /**
     * Установить номер счета.
     *
     * @param string $iInvoice
     */
    public function setInvoice($iInvoice)
    {
        $this->iInvoice = $iInvoice;
    }

    /**
     * Номер счета.
     *
     * @return string
     */
    public function getInvoice()
    {
        return $this->iInvoice;
    }

    /**
     * Установить пароль.
     *
     * @param string $sPassword
     */
    public function setPassword($sPassword)
    {
        $this->sPassword = $sPassword;
    }

    /**
     * Возвращает пароль.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->sPassword;
    }

    /**
     * Код проверки.
     *
     * @param string $sCode
     */
    public function setCode($sCode)
    {
        $this->sCode = $sCode;
    }

    /**
     * Код проверки.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->sCode;
    }

    /**
     * Установить валюту.
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Валюта.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Построение формы.
     *
     * @return string|void
     */
    public function getForm()
    {
        if (!$this->active) {
            return '';
        }

        $this->setDescription(\Yii::t('order', 'order_description', [$this->getOrderId()]));

        $oForm = new Form();
        $oForm->setAction($this->url);

        $aItems = [];
        $aItems['MNT_ID'] = $this->iInvoice;

        $aItems['MNT_TRANSACTION_ID'] = $this->orderId;
        $aItems['MNT_CURRENCY_CODE'] = $this->currency;
        $aItems['MNT_AMOUNT'] = number_format($this->sum, 2, '.', '');
        $aItems['MNT_TEST_MODE'] = (int) $this->bDebug;
        $aItems['MNT_SUBSCRIBER_ID'] = $this->subscriber;
        $aItems['MNT_SIGNATURE'] = $this->createResultSignature();
        $aItems['MNT_DESCRIPTION'] = $this->description;
        $aItems['MNT_TYPE'] = $this->getType();

        $oForm->setFields($aItems);

        return $this->parseForm($oForm);
    }

    /**
     * генерация подписи.
     *
     * @param $sOperationId
     *
     * @return string
     */
    private function createResultSignature($sOperationId = '')
    {
        return md5(
            $this->iInvoice . $this->orderId . $sOperationId . number_format($this->sum, 2, '.', '') .
            $this->currency . $this->subscriber . (int) $this->bDebug . $this->sCode
        );
    }

    /** {@inheritdoc} */
    public function checkResult()
    {
        $sum = Request::getStr('MNT_AMOUNT');
        $orderId = (int) Request::getStr('MNT_TRANSACTION_ID');
        $sOperationId = (int) Request::getStr('MNT_OPERATION_ID');
        $signature = Request::getStr('MNT_SIGNATURE');

        if ($sum != ApiOrder::getOrderSum($orderId, true)) {
            return false;
        }

        $this->setOrderId($orderId);
        $this->setSum($sum);

        return mb_strtolower($signature) == $this->createResultSignature($sOperationId);
    }

    /**
     * Сообщение об успешной оплате.
     *
     * @return string
     */
    public function getSuccess()
    {
        $this->sendFiscalizationData();
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
                    case 'test_life':
                        $this->test_life = $aParam;
                        $this->url = ($this->test_life) ? static::DEVELOPMENT_URL : static::PRODUCTION_URL;
                        $this->wsdl = ($this->test_life) ? static::DEVELOPMENT_WSDL : static::PRODUCTION_WSDL;
                        $this->bDebug = ($this->test_life) ? true : false;
                        break;
                    case 'rk_login':
                        $this->sLogin = $aParam;
                        break;
                    case 'password':
                        $this->sPassword = $aParam;
                        break;
                    case 'invoice':
                        $this->iInvoice = $aParam;
                        break;
                    case 'code':
                        $this->sCode = $aParam;
                        break;
                    case 'vat':
                        $this->vat = $aParam;
                        break;
                }
            }
            $this->bInitParams = true;
        }
    }

    /**
     * Отправка данных для фискализации чека.
     *
     * @see https://www.payanyway.ru/info/p/ru/public/merchants/Assistant54FZ.pdf
     */
    private function sendFiscalizationData()
    {
        $sContent = $this->buildFiscalizationContent();

        $oResponse = \Yii::$app->response;
        $oResponse->headers->add('Content-type', 'application/xml');
        $oResponse->charset = 'UTF-8';
        $oResponse->content = $sContent;
        $oResponse->statusCode = 200;
        $oResponse->send();
    }

    /**
     * Построить контент, передаваемый в payanyway для фискализации чека.
     *
     * @return string
     */
    private function buildFiscalizationContent()
    {
        $iMntId = $this->iInvoice;
        $iMntTransactionId = $this->getOrderId();
        $iMntResultCode = 200;
        $sCheckCode = $this->sCode;

        $aData = [
            'ID' => $iMntId,
            'TRANSACTION_ID' => $iMntTransactionId,
            'RESULT_CODE' => $iMntResultCode,
            'SIGNATURE' => md5($iMntResultCode . $iMntId . $iMntTransactionId . $sCheckCode),
            'ATTRIBUTES' => $this->getMntAttributes(),
        ];

        $sContent = Parser::parseTwig('PayAnyWaySuccessResponse.twig', $aData, __DIR__ . '/templates');

        return $sContent;
    }

    /**
     * Получить атрибуты номенклатуры.
     */
    private function getMntInventoryAttribute()
    {
        $aInventoryItems = [];

        $aOrderGoods = OrderGoods::getByOrderId($this->getOrderId());
        $sPaymentMethod = $this->getPaymentMethod();

        foreach ($aOrderGoods as $aValue) {
            $aInventoryItem['name'] = $this->parseString($aValue['title']);
            $aInventoryItem['price'] = $aValue['price'];
            $aInventoryItem['quantity'] = $aValue['count'];
            $aInventoryItem['vatTag'] = $this->vat;
            $aInventoryItem['pm'] = $sPaymentMethod;

            $sPaymentObject = $this->getPaymentObject($aValue);

            $aInventoryItem['po'] = $sPaymentObject;

            $aInventoryItems[] = $aInventoryItem;
        }

        return json_encode($aInventoryItems);
    }

    /**
     * Вернёт информацию о заказе.
     *
     * @return array
     */
    public function getOrderInfo()
    {
        $aOrderInfo = Order::find()
            ->where('id', $this->getOrderId())
            ->asArray()
            ->getOne();

        return $aOrderInfo;
    }

    /**
     * Получить атрибуты пользователя(email).
     *
     * @return string
     */
    private function getMntCustomerAttribute()
    {
        $aOrderInfo = $this->getOrderInfo();

        return ($aOrderInfo && isset($aOrderInfo['mail'])) ? $aOrderInfo['mail'] : '';
    }

    /**
     * Получить атрибуты доставки(стоимость).
     *
     * @return int
     */
    private function getMntDeliveryAttribute()
    {
        $aOrderInfo = $this->getOrderInfo();

        return $aOrderInfo['price_delivery'];
    }

    /**
     * Получить атрибуты данных, необходимых для фискализации чека.
     *
     * @return array
     */
    private function getMntAttributes()
    {
        $aAttrs = [
            'INVENTORY' => $this->getMntInventoryAttribute(),
            'CUSTOMER' => $this->getMntCustomerAttribute(),
            'DELIVERY' => $this->getMntDeliveryAttribute(),
        ];

        return $aAttrs;
    }

    /**
     * Убирает из строки все html теги и спец. символы (&nbsp; и т.п.).
     *
     * @param $sValue
     *
     * @return string
     */
    private function parseString($sValue)
    {
        $sValueWithoutTags = strip_tags($sValue);
        $sResult = html_entity_decode($sValueWithoutTags);

        return $sResult;
    }
}
