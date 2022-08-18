<?php

namespace skewer\build\Tool\Payments;

use skewer\base\log\Logger;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\base\site_module\Request;
use skewer\build\Adm\Order\ar\Goods;
use skewer\build\Adm\Order\ar\Order;
use skewer\build\Tool\Payments\ar\SberbankPaymentRow;
use skewer\build\Tool\Payments\ar\SberbankPayments;
use yii\helpers\ArrayHelper;

/**
 * Class SberbankPayment.
 */
class SberbankPayment extends Payment
{
    /** url */
    const PRODUCTION_URL = 'https://securepayments.sberbank.ru/payment/rest/';
    const DEVELOPMENT_URL = 'https://3dsec.sberbank.ru/payment/rest/';

    /** Код валюты платежа ISO 4217 */
    const RUB = 643;
    /** Язык в кодировке ISO 639-1*/
    const LANGUAGE = 'ru';

    /** Страна доставки, по умолчанию Россия */
    const COUNTRY_DELIVERY = 'RU';

    /** @var string Логин */
    private $userName = '';

    /** @var string Пароль */
    private $password = '';

    /** @var string URL-адрес REST интерфейса */
    private $url = '';

    /** @var string Идентификатор заказа в сбербанке */
    private $invoice = '';

    /** @var string Сообщение об успешной оплате */
    private $sSuccessMsg = '';

    /** @var string Сообщение об неуспешной оплате */
    private $sFailMsg = 'FAIL';

    /** @var int Флаг Передавать ли товарную корзину для Онлайн кассы */
    private $regOfCart = 0;

    /** @var int Идентификатор Системы налогооблажения */
    private $taxSystem = 0;

    /** @var int Идентификатор Ставки НДС для товаров */
    private $taxTypeForGoods = 0;

    /** @var int Идентификатор Ставки НДС для доставки */
    private $taxTypeForDelivery = 0;

    protected static $aFields = [
        ['username', 'sberbank_field_username', 's', 'str'],
        ['password', 'sberbank_field_password', 's', 'pass'],
        ['reg_of_cart', 'sberbank_field_reg_of_cart', 'i', 'check', ['groupTitle' => 'sberbank_field_use_online_cashbox']],
        ['tax_system', 'sberbank_field_tax_system', 's', 'select', ['groupTitle' => 'sberbank_field_use_online_cashbox', 'emptyStr' => false]],
        ['tax_type_for_goods', 'sberbank_field_tax_type_for_goods', 's', 'select', ['groupTitle' => 'sberbank_field_use_online_cashbox', 'emptyStr' => false]],
        ['tax_type_for_delivery', 'sberbank_field_tax_type_for_delivery', 's', 'select', ['groupTitle' => 'sberbank_field_use_online_cashbox', 'emptyStr' => false]],
    ];

    /** @var array Системы налого облажения
     * Индексы смещены на +1 для нормального отображения в интерфейсе
     */
    protected static $aTaxSystems = [
        0 => 'общая',
        1 => 'упрощённая, доход',
        2 => 'упрощённая, доход минус расход',
        3 => 'единый налог на вменённый доход',
        4 => 'единый сельскохозяйственный налог',
        5 => 'патентная система налогообложения',
    ];

    /** @var array Ставка системы налогаоблажения
     * Индексы смещены на +1 для нормального отображения в интерфейсе
     */
    protected static $aTypeOfTaxSystems = [
        0 => 'без НДС',
        1 => 'НДС по ставке 0%',
        2 => 'НДС чека по ставке 10%',
        4 => 'НДС чека по расчетной ставке 10/110',
        6 => 'НДС чека по ставке 20%',
        7 => 'НДС чека по расчетной ставке 20/120',
    ];

    public function __construct()
    {
        $aParams = ar\Params::find()->where('type', $this->getType())->asArray()->getAll();

        $aParams = ArrayHelper::map($aParams, 'name', 'value');

        $this->initParams($aParams);
    }

    /**
     * @return string Возвращает тип платежной системы
     */
    public function getType()
    {
        return 'sberbank';
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
                        break;
                    case 'username':
                        $this->userName = $aParam;
                        break;
                    case 'password':
                        $this->password = $aParam;
                        break;
                    case 'reg_of_cart':
                        $this->regOfCart = $aParam;
                        break;
                    case 'tax_system':
                        $this->taxSystem = $aParam;
                        break;
                    case 'tax_type_for_goods':
                        $this->taxTypeForGoods = $aParam;
                        break;
                    case 'tax_type_for_delivery':
                        $this->taxTypeForDelivery = $aParam;
                        break;
                }
            }
            $this->bInitParams = true;
        }
    }

    /**
     * Вывод формы для оплаты.
     *
     * @throws \yii\base\Exception
     *
     * @return string
     */
    public function getForm()
    {
        $sInvoice = '';
        $oInvoice = SberbankPayments::getInvoiceById($this->orderId);

        // Если нет записи в sberbank_payments или статус у последнего идентификатора с ошибкой - создаем новую запись
        // и регистрируем заказ в шлюзе
        if (!$oInvoice || (isset($oInvoice->error_code) && $oInvoice->error_code > 0)) {
            $sInvoice = $this->generateInvoice();
            $this->createNewInvoiceSberbank($sInvoice);
            $oInvoice = $this->registerPay($sInvoice);
        } elseif ($oInvoice && isset($oInvoice->invoice) && $oInvoice->invoice) {
            $sInvoice = $oInvoice->invoice;
        }

        // Создаем кнопку для оплаты
        if ($oInvoice && $sInvoice) {
            $errorPayment = Request::getStr('error_payment');

            $oForm = false;
            if ((isset($oInvoice->url_sberbank) && $oInvoice->url_sberbank)
                    && (isset($oInvoice->num_sberbank) && $oInvoice->num_sberbank)) {
                $oForm = $this->createFormForPay($oInvoice->url_sberbank, $oInvoice->num_sberbank);
            }

            if ($errorPayment == 1 || $oInvoice->error_code > 0) { // Если возникла ошибка
                return Parser::parseTwig('form.twig', ['Form' => $oForm, 'error' => 1], BUILDPATH . '/Tool/Payments/templates/');
            }  // Если регистрация прошла упешно и осталось лишь только оплатить

            return $oForm instanceof Form
                ? $this->parseForm($oForm)
                : false;
        }

        return false;
    }

    /**
     * Возвращает список полей для редактирования.
     *
     * @return array
     */
    public static function getFields()
    {
        $aFields = [];
        if (count(static::$aFields)) {
            foreach (static::$aFields as $aField) {
                $aField[1] = \Yii::t('payments', $aField[1]);
                if (isset($aField[4])) {
                    foreach ($aField[4] as $iKey => $sName) {
                        if (isset($aField[4]['groupTitle'])) {
                            $aField[4]['groupTitle'] = \Yii::t('Payments', $aField[4]['groupTitle']);
                        }

                        if ($aField[0] == 'tax_system') {
                            $aField[4]['show_val'] = self::$aTaxSystems;
                        }

                        if (($aField[0] == 'tax_type_for_goods') || ($aField[0] == 'tax_type_for_delivery')) {
                            $aField[4]['show_val'] = self::$aTypeOfTaxSystems;
                        }
                    }
                }
                $aFields[] = $aField;
            }
        }

        return $aFields;
    }

    /**
     * Метод проверки результата об оплате, полученного от системы платежей
     * * Возвращаемые значения: Если true то заказ оплачен. false -- заказ отменён. Иначе не менять статус заказа.
     *
     * @return bool|mixed
     */
    public function checkResult()
    {
        $num_sberbank = Request::getStr('orderId');

        if (!$num_sberbank) {
            return false;
        }

        $oInvoice = SberbankPayments::getInvoiceByIdSberbank($num_sberbank);
        if (!$oInvoice) {
            return false;
        }

        $this->setOrderId($oInvoice->order_id);

        // Формируем поля для запроса к шлюзу
        $aParams4Sber = [
              'userName' => $this->userName,
              'password' => $this->password,
              'orderId' => $num_sberbank,
              'language' => self::LANGUAGE,
          ];

        $result = $this->makeCurlRequest('getOrderStatus.do', $aParams4Sber);

        if (isset($result['error'])) {
            Logger::error('Ошибка при запросе к Шлюзу сбербанка: "' . $result['error'] . '"');

            return false;
        }

        $aResponse = json_decode($result, true);

        if (isset($aResponse['ErrorCode']) && $aResponse['ErrorCode'] == 0) { // Оплата прошла Успешно
            // Сумма в рублях
            $fSumInRub = $aResponse['depositAmount'] / 100;

            $this->setSum($fSumInRub);

            $oInvoice->amount = $fSumInRub;
            $oInvoice->error_code = $aResponse['ErrorCode'];
            $oInvoice->error_message = $aResponse['ErrorMessage'];
            $oInvoice->save();

            $this->sSuccessMsg = 'success';

            return true;
        }

        if (isset($aResponse['ErrorCode']) && $aResponse['ErrorCode'] > 0) { // Неуспешно
            $oInvoice->error_code = $aResponse['ErrorCode'];
            $oInvoice->error_message = $aResponse['ErrorMessage'];
            $oInvoice->save();

            $sToken = '';
            if ($oInvoice->order_id) {
                $aOrder = Order::find()->where('id', $oInvoice->order_id)->asArray()->getOne();
                if (isset($aOrder['token'])) {
                    $sToken = $aOrder['token'];
                }
            }

            if ($sToken) {
                $sCartUrl = \Yii::$app->sections->getValue('cart');
                $sCartUrl = \Yii::$app->router->rewriteURL('[' . $sCartUrl . ']');
                \Yii::$app->response->redirect($sCartUrl . 'done/?token=' . $sToken . '&error_payment=1')->send();
                exit;
            }
        }

        return false;
    }

    /**
     * Метод выполняемый после прохождения оплаты.
     *
     * @param $sResult
     */
    public function afterPayment($sResult)
    {
        if ($sResult == $this->sSuccessMsg) {
            $sURL = \Yii::$app->router->rewriteURL('[' . \Yii::$app->sections->getValue('payment_success') . ']');
        } else {
            $sURL = \Yii::$app->router->rewriteURL('[' . \Yii::$app->sections->getValue('payment_fail') . ']');
        }

        \Yii::$app->response->redirect($sURL . '?orderId=' . $this->orderId);
    }

    /**
     * Сообщение о неуспешной оплате.
     *
     * @return string
     */
    public function getFail()
    {
        return $this->sFailMsg;
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
     * Устанавливает номер заказа.
     *
     * @param int $orderId Номер заказа
     */
    public function setOrderId($orderId)
    {
        parent::setOrderId($orderId);
    }

    /**
     * Регистрация заказа в платежном шлюзе сбербанка.
     *
     * @param $sInvoice string Уникального номера заказа
     *
     * @throws \yii\base\Exception
     *
     * @return array|bool|\skewer\base\orm\ActiveRecord
     */
    private function registerPay($sInvoice)
    {
        if (!$sInvoice) {
            return false;
        }

        $oInvoice = SberbankPayments::getInvoiceByInvoice($sInvoice);

        if (!$oInvoice instanceof SberbankPaymentRow) {
            return false;
        }

        $this->setOrderId($oInvoice->order_id);
        $this->setSum($oInvoice->amount);

        // Формируем параметры для запроса в шлюз сбербанка
        $aParams4Sber = [
            'userName' => $this->userName,
            'password' => $this->password,
            'orderNumber' => $sInvoice,
            'amount' => $this->sum * 100,
            'currency' => self::RUB,
            'language' => self::LANGUAGE,
            'returnUrl' => Site::httpDomain() . '/payment.php?MNT_TYPE=sberbank',
            'failUrl' => Site::httpDomain() . '/payment.php?MNT_TYPE=sberbank',
            'description' => str_replace(['+', '%', "\r", "\n"], '', $this->cutName($oInvoice->description, 99)),
            'pageView' => 'DESKTOP',
        ];

        if ($this->regOfCart) {
            $aParams4Sber = $this->createOrderToCashbox($aParams4Sber);
        }

        $result = $this->makeCurlRequest('register.do', $aParams4Sber);

        if (isset($result['error'])) {
            Logger::error('Ошибка при запросе к Шлюзу сбербанка: "' . $result['error'] . '"');

            return false;
        }

        $aResponse = json_decode($result, true);

        // Заказ в шлюзе был успешно создан
        if (isset($aResponse['formUrl'], $aResponse['orderId'])) {
            $oInvoice->num_sberbank = $aResponse['orderId'];
            $oInvoice->url_sberbank = $aResponse['formUrl'];
            $oInvoice->save();
        }

        if (isset($aResponse['errorCode'], $aResponse['errorMessage'])) {
            $oInvoice->error_code = $aResponse['errorCode'];
            $oInvoice->error_message = $aResponse['errorMessage'];
            $oInvoice->save();

            Logger::error('Ошибка от шлюза при регистрации № ' . $aResponse['errorCode'] . ' ' . $aResponse['errorMessage']);
        }

        return ($oInvoice) ? $oInvoice : false;
    }

    /**
     * Добавление данных ОФД для онлайн кассы в сбере.
     *
     * @param $aParamsRegister array Общая инфа о заказе для оплаты
     *
     * @throws \yii\base\Exception
     *
     * @return array Слитый массив инфы о заказе, с перечнем товаров
     */
    private function createOrderToCashbox($aParamsRegister)
    {
        $aOrderInfo = Order::find()->where('id', $this->orderId)->asArray()->getOne();
        $aGoods = Goods::find()->where('id_order', $this->orderId)->asArray()->getAll();

        if ($aOrderInfo && $aGoods) {
            $aOrderBundle = [
                'orderCreationDate' => (isset($aOrderInfo['date'])) ? strtotime($aOrderInfo['date']) : time(),
                'customerDetails' => [
                    'email' => (isset($aOrderInfo['mail'])) ? $this->cutName($aOrderInfo['mail'], 40) : '',
                    'phone' => (isset($aOrderInfo['phone'])) ? $this->phoneToNumber($aOrderInfo['phone']) : '',
                ],
            ];

            $sPaymentMethod = $this->getPaymentMethod();

            if ($aOrderInfo['price_delivery'] > 0) {
                $aDeliveryGood = $this->createDeliveryGood($aOrderInfo['price_delivery']);
                $aGoods[] = $aDeliveryGood;
            }

            foreach ($aGoods as $key => $item) {
                $sPaymentObject = $this->getPaymentObject($item);

                $aOrderBundle['cartItems']['items'][] = [
                    'positionId' => $key + 1,
                    'name' => (isset($item['title'])) ? $this->cutName($item['title']) : '',
                    'quantity' => [
                        'value' => (isset($item['count'])) ? $item['count'] : '',
                        'measure' => 'товар',
                    ],
                    'itemAmount' => $item['total'] * 100,
                    'itemCurrency' => self::RUB,
                    'itemCode' => (isset($item['id_goods'])) ? (isset($item['size']) && $item['size']) ? $item['id_goods'] . '#' . $item['size'] : $item['id_goods'] : '',
                    'tax' => [
                        'taxType' => ($item['tax'] ?? $this->taxTypeForGoods),
                    ],
                    'itemPrice' => (isset($item['price'])) ? $item['price'] * 100 : '',
                    'itemAttributes' => [
                        'attributes' => [
                            [
                                'name' => 'paymentMethod',
                                'value' => "{$sPaymentMethod}",
                            ],
                            [
                                'name' => 'paymentObject',
                                'value' => "{$sPaymentObject}",
                            ],
                        ],
                    ],
                ];
            }
        } else {
            return $aParamsRegister;
        }

        $aParamsRegister['taxSystem'] = $this->taxSystem;
        $aParamsRegister['orderBundle'] = $this->jsonEncode($aOrderBundle);

        return $aParamsRegister;
    }

    /**
     * Создание нового Заказа для сбербанка.
     *
     * @param $sInvoice string Идентификатор сбербанка
     *
     * @return bool
     */
    private function createNewInvoiceSberbank($sInvoice)
    {
        if (!$sInvoice || !$this->sum) {
            return false;
        }

        $oPayment = SberbankPayments::getNewRow();
        $oPayment->order_id = $this->orderId;
        $this->invoice = $sInvoice;
        $oPayment->invoice = $this->invoice;
        $oPayment->description = $this->generateDescription($this->orderId);
        $oPayment->amount = $this->sum;
        $oPayment->add_date = date('Y-m-d H:i:s');

        return $oPayment->save();
    }

    /**
     * Создаем описание заказа для сбербанка.
     *
     * @param string $iOrderId
     *
     * @return string
     */
    private function generateDescription($iOrderId)
    {
        return 'Оплата по счету № ' . $iOrderId;
    }

    /**
     * Генерация ункального номера заказа с сайта для сбербанка.
     *
     * @return string номер закза вида [номер заказа]#[ГодМесяцДеньЧасМинута]
     */
    private function generateInvoice()
    {
        return $this->orderId . '#' . date('YmdHis');
    }

    /**
     * Обрезаем строку на указанное количество символов.
     *
     * @param $name string Строка
     * @param int $length integer Длинна строки
     *
     * @return string Выходная строка
     */
    private function cutName($name, $length = 100)
    {
        if (mb_strwidth($name, 'utf-8') > $length) {
            $name = mb_strimwidth($name, 0, $length, '…', 'utf-8');
        }

        return $name;
    }

    /**
     * Преобразовнаие в JSON параметр для передачи в запросе.
     *
     * @param $Data mixed Входные данные
     *
     * @return string Кодированные данные
     */
    private function jsonEncode($Data)
    {
        return json_encode($Data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Перевод телефона в число.
     *
     * @param $sPhoneNumber string Номер телефона, возможно с маской
     *
     * @return int Номер телефона
     */
    private function phoneToNumber($sPhoneNumber)
    {
        $sPhoneNumber = preg_replace('/\+?([0-9-() ]+)_/', '$1', $sPhoneNumber);
        $sPhoneNumber = str_replace([' ', '-', '(', ')', '+'], '', $sPhoneNumber);
        $sPhoneNumber = $this->cutName($sPhoneNumber, 40);

        return $sPhoneNumber;
    }

    /**
     * Добавление к название товара его размера, если есть.
     *
     * @param $aItem array Позиция товар
     *
     * @return string Наименование товара с размером, или без
     */
    private function makeTitleGoodsWithSize($aItem)
    {
        if (!$aItem) {
            return '';
        }

        $sTitle = (isset($aItem['title']) && $aItem['title']) ? $aItem['title'] : '';
        $sSize = (isset($aItem['size']) && $aItem['size']) ? $aItem['size'] : '';

        if ($sSize) {
            $sTitle .= '. ' . \Yii::t('Payments', 'sberbank_part_title_size') . ' ' . $sSize;
        }

        return $sTitle;
    }

    /**
     * Создаем запрос к шлюзу сбербанка.
     *
     * @param $sMethodName string REST метод
     * @param $aParams array Массив полей для отправки
     *
     * @return array|mixed Результат запроса
     */
    private function makeCurlRequest($sMethodName, $aParams)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url . $sMethodName);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($aParams));

        $result = curl_exec($curl);
        $error_no = curl_errno($curl);
        $error_msg = curl_error($curl);
        curl_close($curl);

        if ($error_no) {
            return ['error' => $error_msg];
        }

        return $result;
    }

    /**
     * Создание кнопки для оплаты в шлюзе сбербанка.
     *
     * @param $sUrlSberbank string ссылка на форму оплаты сбербанка
     * @param $sNumSberbank string нуникальный номер заказа сбербанка
     *
     * @return Form object объект формы для парсинга
     */
    private function createFormForPay($sUrlSberbank, $sNumSberbank)
    {
        $oForm = new Form();
        $oForm->setAction($sUrlSberbank);
        $oForm->setMethod('GET');
        $oForm->setFields(['mdOrder' => $sNumSberbank]);

        return $oForm;
    }

    /**
     * Проверка, подключена ли фискализация.
     *
     * @return bool
     */
    public function checkRegOfCart()
    {
        return (bool) $this->regOfCart;
    }

    /**
     * Метод для возврата денежных средств.
     *
     * @param $sInvoice string Уникальный номер заказа в сбербанке
     * @param $iAmount integer Сумма возврата в рублях
     * @param array $aGoods array Товары для частичного возврата
     *
     * @return bool Результат возврата
     */
    public function returnCash($sInvoice, &$iAmount, $aGoods = [])
    {
        if (!$sInvoice || !$iAmount) {
            return false;
        }

        $aPaymentSberbank = SberbankPayments::getInvoiceByInvoice($sInvoice, true);

        // Проверяем действительно ли был успешно завершен платеж
        if (isset($aPaymentSberbank['error_code']) && $aPaymentSberbank['error_code'] == '0') {
            $aParams = [
                'userName' => $this->userName,
                'password' => $this->password,
                'orderId' => $aPaymentSberbank['num_sberbank'],
                'amount' => $iAmount * 100,
            ];

            // Частичный возврат
            if ($aGoods) {
                $aRefundItems = [];
                $iTotalAmount = 0;
                foreach ($aGoods as $item) {
                    if (isset($item['count'], $item['price'])) {
                        $iItemAmount = $item['count'] * $item['price'];
                        $iTotalAmount += $iItemAmount;

                        $aRefundItems['items'][] = [
                            'positionId' => (isset($item['id'])) ? $item['id'] : '',
                            'name' => (isset($item['title'])) ? $this->cutName($this->makeTitleGoodsWithSize($item)) : '',
                            'quantity' => [
                                'value' => $item['count'],
                                'measure' => 'товар',
                            ],
                            'itemAmount' => $iItemAmount * 100,
                            'itemCurrency' => self::RUB,
                            'itemCode' => (isset($item['id_goods'])) ? (isset($item['size']) && $item['size']) ? $item['id_goods'] . '#' . $item['size'] : $item['id_goods'] : '',
                            'tax' => [
                                'taxType' => $this->taxTypeForGoods,
                            ],
                            'itemPrice' => (isset($item['price'])) ? $item['price'] * 100 : '',
                        ];
                    }
                }
                $aParams['refundItems'] = $this->jsonEncode($aRefundItems);
                $aParams['amount'] = $iTotalAmount * 100;
                $iAmount = $iTotalAmount;
            }

            $result = $this->makeCurlRequest('refund.do', $aParams);

            // Ошибка при запросе в шлюз
            if (isset($result['error'])) {
                Logger::error('Ошибка при возврате денег(сбербанк): ' . $result['error']);

                return false;
            }

            $aResults = json_decode($result, true);

            if (isset($aResults['errorCode'], $aResults['errorMessage'])) {
                $this->orderId = (isset($aPaymentSberbank['order_id'])) ? $aPaymentSberbank['order_id'] : random_int(10, 100);
                $sNewInvoice = $this->generateInvoice();
                $this->sum = $aPaymentSberbank['amount'];
                if ($this->createNewInvoiceSberbank($sNewInvoice)) {
                    $oNewSberPay = SberbankPayments::getInvoiceByInvoice($sNewInvoice);
                    $oNewSberPay->num_sberbank = $aPaymentSberbank['num_sberbank'];
                    $oNewSberPay->description = 'Возврат денежных средств по счету № ' . $this->orderId;
                    $oNewSberPay->amount = $iAmount * 100;
                    $oNewSberPay->error_code = $aResults['errorCode'];
                    $oNewSberPay->error_message = $aResults['errorMessage'];

                    if ($oNewSberPay->save() && $aResults['errorCode'] == '0') {
                        return true;
                    }

                    return false;
                }
            }
        }

        return false;
    }

    protected function getPaymentObjects()
    {
        return [
            'commodity' => 1,
            'excise' => 2,
            'job' => 3,
            'service' => 4,
            'gambling_bet' => 5,
            'gambling_prize' => 6,
            'lottery' => 7,
            'lottery_prize' => 8,
            'intellectual_activity' => 9,
            'payment' => 10,
            'agent_commission' => 11,
            'composite' => 12,
            'another' => 13,
            'property_right' => 13,
            'non-operating_gain' => 13,
            'insurance_premium' => 13,
            'sales_tax' => 13,
            'resort_fee' => 13,
        ];
    }

    protected function getPaymentMethods()
    {
        return [
            'full_prepayment' => 1,
            'prepayment' => 2,
            'advance' => 3,
            'full_payment' => 4,
            'partial_payment' => 5,
            'credit' => 6,
            'credit_payment' => 7,
        ];
    }

    private function createDeliveryGood($price)
    {
        return [
            'id_goods' => 'delivery',
            'title' => 'delivery',
            'count' => 1,
            'total' => $price,
            'price' => $price,
            'payment_object' => $this->getPaymentObjects()['service'],
            'tax' => $this->taxTypeForDelivery,
        ];
    }
}
