<?php

namespace skewer\build\Tool\Payments;

use skewer\base\site_module\Request;
use skewer\build\Adm\Order\Api as ApiOrder;
use skewer\build\Adm\Order\ar\Goods;
use skewer\build\Adm\Order\ar\Order;

/**
 * Class RobokassaPayment.
 */
class RobokassaPayment extends Payment
{
    /** Адреса шлюзов */
    const PRODUCTION_URL = 'https://merchant.roboxchange.com/Index.aspx';

    /** @var string Логин */
    private $login = '';

    /** @var string Пароль 1 */
    private $password1 = '';

    /** @var string Пароль 2 */
    private $password2 = '';

    /** @var string Язык интерфейса */
    private $language = 'ru';

    /** @var string Выбранный тип оплаты. Если пусто, то пользователь сам выбирает способ оплаты */
    private $ecurrency;

    /** @var string Кодировка */
    private $encoding = 'utf-8';

    /** @var string URL для отправки формы */
    private $url;

    /** @var string Система налогообложения */
    private $sno = 'osn';

    /** @var string Номер налога в ККТ */
    private $tax = 'none';

    /** @var string Заказ в формате json */
    private $jsonOrder = '';

    /** @var bool Фискализировать чеки? */
    private $bFiscalization_check = false;

    /**
     * @var array Список полей для редактирования
     */
    protected static $aFields = [
        ['rk_login', 'Robokassa_login_field', 's', 'str'],
        ['rk_password1', 'Robokassa_password1_field', 's', 'pass'],
        ['rk_password2', 'Robokassa_password2_field', 's', 'pass'],
        ['fiscalization_check', 'fiscalization_check_field', 'check', 'check', ['subtext' => 'Для клиентов Robokassa, выбравших для себя Облачное или Кассовое решение']],
        ['sno', 'taxSystem_field', 's', 'select', ['osn' => 'taxSystem_1', 'usn_income' => 'taxSystem_2', 'usn_income_outcome' => 'taxSystem_3', 'envd' => 'taxSystem_4', 'esn' => 'taxSystem_5', 'patent' => 'taxSystem_6']],
        ['tax', 'tax_field', 's', 'select', ['none' => 'tax_none', 'vat0' => 'tax_vat0', 'vat10' => 'tax_vat10', 'vat20' => 'tax_vat20', 'vat110' => 'tax_vat110', 'vat120' => 'tax_vat120']],
    ];

    public function getType()
    {
        return 'robokassa';
    }

    /**
     * Конструктор
     */
    public function init()
    {
    }

    /**
     * Устанавливает логин.
     *
     * @param string $rk_login
     */
    public function setLogin($rk_login)
    {
        $this->login = $rk_login;
    }

    /**
     * Логин.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Устанавливает пароль 1.
     *
     * @param string $rk_password1
     */
    public function setPassword1($rk_password1)
    {
        $this->password1 = $rk_password1;
    }

    /**
     * Устанавливает пароль 2.
     *
     * @param string $rk_password2
     */
    public function setPassword2($rk_password2)
    {
        $this->password2 = $rk_password2;
    }

    /**
     * Возвращает способ оплаты.
     *
     * @return string Способ оплаты
     */
    public function getEcurrency()
    {
        return $this->ecurrency;
    }

    /**
     * Устанавливает способ оплаты.
     *
     * @param string $ecurrency Способ оплаты
     */
    public function setEcurrency($ecurrency)
    {
        $this->ecurrency = $ecurrency;
    }

    /**
     * Возвращает кодировку.
     *
     * @return string Кодировка
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Устанавливает кодировку.
     *
     * @param string $encoding Кодировка
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Возвращает язык интерфейса.
     *
     * @return string Язык интерфейса
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Устанавливает язык интерфейса.
     *
     * @param string $language Язык интерфейса
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Возвращает URL шлюза.
     *
     * @return string URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /** {@inheritdoc}*/
    public function getSum()
    {
        return number_format($this->sum, 2, '.', '');
    }

    /**
     * Пользовательские параметры, передаваемые вместе с остальными данными платежа
     * Учавствуют в формировании сигнатуры.
     */
    public function getUserParams()
    {
        $aParams = [
            'shp_type' => $this->getType(),
        ];

        // Пользовательские параметры обязательны должны быть отсортированы в алфавитном порядке
        ksort($aParams);

        return $aParams;
    }

    /**
     * Генерирует подпись для оплаты.
     *
     * @return string
     */
    public function createPaymentSignature()
    {
        $aParams = [
            $this->login,
            $this->getSum(),
            $this->getOrderId(),
        ];

        // В тестовом режиме фискальные данные не передаются
        if ($this->bFiscalization_check && !$this->test_life) {
            array_push($aParams, $this->getJsonOrder());
        }

        array_push($aParams, $this->password1);

        // Дополнительные пользовательские параметры
        $aUserParams = $this->getUserParams();

        foreach ($aUserParams as $sName => $sVal) {
            $aParams[] = sprintf('%s=%s', $sName, $sVal);
        }

        $sParams = implode(':', $aParams);

        return md5($sParams);
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

        $oForm = new Form();
        $oForm->setAction($this->url);

        $aItems = [];
        $aItems['MrchLogin'] = $this->login;
        $aItems['OutSum'] = $this->getSum();
        $aItems['InvId'] = $this->orderId;
        $aItems['Desc'] = $this->description;
        $aItems['SignatureValue'] = $this->createPaymentSignature();
        $aItems['IncCurrLabel'] = $this->ecurrency;
        $aItems['Culture'] = $this->language;
        $aItems['encoding'] = $this->encoding;
        $aItems['IsTest'] = (int) $this->test_life;

        $aUserParams = $this->getUserParams();
        $aItems = array_merge($aItems, $aUserParams);

        $aClient = Order::find()->where('id', $this->getOrderId())->asArray()->getOne();
        $aItems['Email'] = ($aClient && isset($aClient['mail'])) ? $aClient['mail'] : '';

        // Фискальные данные(данные для чека) в тестовом режиме не передаются
        if ($this->bFiscalization_check && !$this->test_life) {
            $aItems['Receipt'] = $this->getJsonOrder();
        }

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
        $aParams = [
            $sum,
            $orderId,
            $this->password2,
        ];

        $aUserParams = $this->getUserParams();

        foreach ($aUserParams as $sName => $sVal) {
            $aParams[] = sprintf('%s=%s', $sName, $sVal);
        }

        $sParams = implode(':', $aParams);

        return md5($sParams);
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
        return 'OK' . $this->getOrderId();
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
                        $this->url = static::PRODUCTION_URL;
                        break;
                    case 'rk_login':
                        $this->login = $aParam;
                        break;
                    case 'rk_password1':
                        $this->password1 = $aParam;
                        break;
                    case 'rk_password2':
                        $this->password2 = $aParam;
                        break;
                    case 'sno':
                        $this->sno = $aParam;
                        break;
                    case 'tax':
                        $this->tax = $aParam;
                        break;
                    case 'fiscalization_check':
                        $this->bFiscalization_check = (bool) $aParam;
                        break;
                }
            }
            $this->bInitParams = true;
        }
    }

    /**
     * Данные для чека.
     *
     * @return string
     */
    public function getJsonOrder()
    {
        if ($this->jsonOrder) {
            return $this->jsonOrder;
        }

        $aOrderResult = [];
        $aResult['sno'] = $this->sno;

        $sPaymentMethod = $this->getPaymentMethod();

        $aOrder = Goods::find()->where('id_order', $this->getOrderId())->asArray()->getAll();
        foreach ($aOrder as $aValue) {
            $aOrderCheck['name'] = $aValue['title'];
            $aOrderCheck['quantity'] = $aValue['count'];
            $aOrderCheck['sum'] = $aValue['total'];
            $aOrderCheck['tax'] = $this->tax;
            $aOrderCheck['payment_method'] = $sPaymentMethod;

            $sPaymentObject = $this->getPaymentObject($aValue);

            $aOrderCheck['payment_object'] = $sPaymentObject;
            $aOrderResult[] = $aOrderCheck;
        }
        $aResult['items'] = $aOrderResult;
        $this->jsonOrder = json_encode($aResult);

        return $this->jsonOrder;
    }
}
