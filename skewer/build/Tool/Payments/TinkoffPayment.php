<?php

namespace skewer\build\Tool\Payments;

use skewer\base\log\Logger;
use skewer\build\Adm\Order\ar\Goods;
use skewer\build\Adm\Order\ar\Order;
use skewer\build\Adm\Order\ar\OrderRow;
use yii\base\Exception;

/**
 * Class TinkoffPayment.
 */
class TinkoffPayment extends Payment
{
    const LIVE_GATEWAY = 'https://securepay.tinkoff.ru/';

    const TEST_GATEWAY = 'https://rest-api-test.tinkoff.ru/';

    /** IP серверов Тинкова*/
    const GATEWAY_IP = '91.194.226';

    /** @var string Ключ терминала */
    private $terminalKey = '';

    /** @var string Пароль от терминала */
    private $password = '';

    /** @var string Команда для исполнения, по умолчанию проверка статуса платежа, может быть изменена */
    public $sAction = 'checkConfirmedPay';

    /** @var array Контейнер данных для храения данных полученных в результате парсинга ответа */
    private $aResData = [];

    /** @var bool Позволяет выполнить один запрос, много тестов */
    public $bExpressTest = false;

    /**
     * Отдает домен платежного шлюза
     * тестовый / живой в зависимости от настроек.
     *
     * @return string
     */
    protected function getGatewayUrl()
    {
        return $this->test_life ? static::TEST_GATEWAY : static::LIVE_GATEWAY;
    }

    /**
     * Шлюз инициализации платёжной сессии
     * тестовый / живой в зависимости от настроек.
     *
     * @return string
     */
    protected function getInitGateway()
    {
        return $this->getGatewayUrl() . 'rest/Init';
    }

    /**
     * Шлюз подверждения списания денеждных средств
     * используется при двухстадийном способе, в данный момент функционала под этот шлюз нет
     * тестовый / живой в зависимости от настроек.
     *
     * @return string
     */
    protected function getConfirmGateway()
    {
        return $this->getGatewayUrl() . 'rest/Confirm';
    }

    /**
     * Шлюз отмены платёжной сессии
     * тестовый / живой в зависимости от настроек.
     *
     * @return string
     */
    protected function getCancelGateway()
    {
        return $this->getGatewayUrl() . 'rest/Cancel';
    }

    /**
     * Шлюз проверки статуса платежа
     * тестовый / живой в зависимости от настроек.
     *
     * @return string
     */
    protected function getGetStateGateway()
    {
        return $this->getGatewayUrl() . 'rest/GetState';
    }

    /**
     * Возращает успешные статусы отмены платежа.
     *
     * @return array
     */
    private function getStatusSuccessCancelPay()
    {
        // Платёж отменён, Средства разблокированы, Средства возвращены
        return ['CANCELED', 'REVERSED', 'REFUNDED'];
    }

    /**
     * Возращает успешные статусы начала платёжной сессии.
     *
     * @return array
     */
    private function getStatusSuccessInitPay()
    {
        return ['NEW'];
    }

    /**
     * Возаращает успешные статусы успешного списания денежных средств.
     *
     * @return array
     */
    private function getStatusSuccessConfirmedPay()
    {
        return ['CONFIRMED'];
    }

    /**
     * Возвращает успешные статусы успешного блокирования денежных средств в банке эмитента
     * Короче деньги заблокированы, но не списаны еще.
     * Метод холдирования вызывается автоматически после успешного отправки платёжных данных для одностадийного метода оплаты.
     *
     * @return array
     */
    private function getStatusSuccessHoldMoneyPay()
    {
        return ['AUTHORIZED'];
    }

    /**
     * Отдаёт минимальный набор обязательных полей приходящих с POST для нотификаций.
     *
     * @return array
     */
    private function getRequiredFieldPostNotice()
    {
        return ['TerminalKey', 'OrderId', 'Success', 'Status', 'PaymentId', 'Amount', 'Token'];
    }

    /**
     * @var [] Список полей для редактирования
     */
    protected static $aFields = [
        ['terminalKey', 'Tinkoff_terminalKey_field', 's', 'str'],
        ['password', 'Tinkoff_password_field', 's', 'pass'],
    ];

    /**
     * Статусы заказов привязанные к методам
     * ['статус_заказа' => 'метод__первая_буква_с_нижнего_регистра' ].
     *
     * @return array
     */
    private function getStatusesMethods()
    {
        return ['cancel' => 'cancelPay'];
    }

    /**
     * Проверяет на возможность выполнить команду, предупреждающий метод.
     *
     * @param $methodName
     *
     * @return bool
     */
    public function bindedStatusMethod($methodName)
    {
        return array_key_exists($methodName, $this->getStatusesMethods());
    }

    /**
     * Выполняет операцию над заказом, завершающий метод.
     *
     * @param $methodName
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function executeStatusMethod($methodName)
    {
        $methods = $this->getStatusesMethods();
        $convertName = mb_convert_case(mb_substr($methods[$methodName], 0, 1), MB_CASE_UPPER, 'UTF-8') . mb_substr($methods[$methodName], 1);
        $statuses = 'getStatusSuccess' . $convertName;
        //Чтобы не гонять методы туда обратно проверем через чекера сам статус заказа.
        $this->sAction = 'check' . $convertName;
        $bRes = $this->checkResult();
        if (!$bRes) { //статус заказа не соответствует
            $sRes = $this->{$methods}[$methodName]();
            $aStatuses = $this->{$statuses}();

            return $this->parseAnswer($sRes, $aStatuses);
        }

        return $bRes;
    }

    /**
     * Тип агрегатора систем оплат
     *
     * @return string
     */
    public function getType()
    {
        return 	'tinkoff';
    }

    /**
     * Сообщение об успешной оплате.
     *
     * @return string
     */
    public function getSuccess()
    {
        return 'OK';
    }

    /**
     * Сообщение о неуспешной оплате.
     *
     * @return string
     */
    public function getFail()
    {
        return false;
    }

    /**
     * Вывод формы для оплаты
     * Должен сначала сделать запрос и получить ссылку на форму оплаты,
     * и сформировать кнопку для перехода
     * Присутствует защита от разногласий данных с нашей БД и БД Платёжной системы.
     *
     * @throws Exception
     *
     * @return string
     */
    public function getForm()
    {
        if (!$this->active) {
            return '';
        }
        $this->bExpressTest = true;
        $this->sAction = 'checkCancelPay';
        $bRes = $this->checkResult();
        if ($bRes) {
            return \Yii::t('payments', 'payment_canceled');
        }

        $this->sAction = 'checkConfirmedPay';
        $bRes = $this->checkResult();
        if ($bRes) {
            return \Yii::t('payments', 'paid');
        }

        $this->sAction = 'checkHoldPay';
        $bRes = $this->checkResult();
        if ($bRes) {
            return \Yii::t('payments', 'not_paid');
        }

        $sRes = $this->initPay();
        $bRes = $this->parseAnswer($sRes, $this->getStatusSuccessInitPay(), true);

        if ($bRes) {
            /** @var OrderRow $oOrder */
            $oOrder = Order::find($this->getOrderId());
            $oOrder->paymentId = $this->aResData['PaymentId'];
            $oOrder->save();
            $oForm = new Form();
            $oForm->setAction($this->aResData['PaymentURL']);
            $oForm->setMethod('POST');

            return $this->parseForm($oForm);
        }
        if (isset($this->aResData['Details'])) {
            \Yii::error('Order №' . $this->getOrderId() . ': ' . $this->aResData['Details']);
        }

        return \Yii::t('payments', 'connection_error');
    }

    //func

    /**
     * Возвращает сумму в копейках.
     *
     * @return int
     */
    public function getSum()
    {
        return (int) ((string) ($this->sum * 100));
    }

    public function initParams($aParams = [])
    {
        foreach ($aParams as $sKey => $aParam) {
            switch ($sKey) {
                case 'active':
                    $this->active = $aParam;
                    break;
                case 'test_life':
                    $this->test_life = $aParam;
                    break;
                case 'terminalKey':
                    $this->terminalKey = $aParam;
                    break;
                case 'demoTerminalKey':
                    $this->terminalKey = $aParam;
                    break;
                case 'password':
                    $this->password = $aParam;
                    break;
            }
        }
    }

    /**
     * Проверяет массив данных на необходимомые поля.
     *
     * @param $aFieldsRequired
     * @param $aData
     *
     * @return bool
     */
    protected function checkRequiredFields($aFieldsRequired, $aData)
    {
        $aKeysData = array_keys($aData);
        foreach ($aFieldsRequired as $field) {
            if (!in_array($field, $aKeysData)) {
                return false;
            }
        }

        return true;
    }

    /** Проверка состояния заказа
     * @throws Exception
     */
    public function checkResult()
    {
        try {
            $postData = \Yii::$app->request->post();

            if (!$this->checkRequiredFields($this->getRequiredFieldPostNotice(), $postData)) {
                $postData = [];
            }

            if (count($postData)) {
                $oldToken = '';
                if (isset($postData['Token'])) {
                    $oldToken = $postData['Token'];
                    unset($postData['Token']);
                }

                $postData['Password'] = $this->password;

                $aDataOrder = $this->buildData($postData);

                if ($aDataOrder['Token'] !== $oldToken) {
                    throw new Exception('Invalid token');
                }
                if ($this->terminalKey !== $aDataOrder['TerminalKey']) {
                    throw new Exception('Invalid terminalkey');
                }
                if ($aDataOrder['Success'] != true) {
                    throw new Exception('Invalid status');
                }
                /** @var OrderRow $oOrder */
                $oOrder = Order::find($aDataOrder['OrderId']);

                if ($oOrder === null || $oOrder->paymentId !== $aDataOrder['PaymentId']) {
                    throw new Exception('Invalid order');
                }
                $aGoods = Goods::find()
                    ->fields('total')
                    ->where('id_order', $oOrder->id)
                    ->asArray()->getAll();

                foreach ($aGoods as $goods) {
                    $this->sum += $goods['total'];
                }

                if ($this->getSum() != $aDataOrder['Amount']) {
                    throw new Exception('Invalid amount');
                }
                $this->setOrderId($aDataOrder['OrderId']);

                if ($aDataOrder['Status'] == 'AUTHORIZED' or $aDataOrder['Status'] == 'CONFIRMED') { //Оплачен
                    return true;
                }   //Пока костылём
                echo $this->getSuccess();
                exit;
            }

            /**
             * Прямые проверки статусов.
             */
            /** @var OrderRow $oOrder */
            $oOrder = Order::find($this->getOrderId());

            if (!$oOrder) {
                throw new Exception('Invalid order');
            }

            if ($this->bExpressTest) {
                if (count($this->aResData) and isset($this->aResData['PaymentId']) and $oOrder->paymentId === $this->aResData['PaymentId']) { //проверка на текущий заказ
                    $res = $this->aResData;
                } else {
                    $res = $this->getStatusPay($oOrder->paymentId);
                    $this->parseAnswer($res, [], true);
                }
            } else {
                $this->aResData = [];
                $res = $this->getStatusPay($oOrder->paymentId);
            }

            switch ($this->sAction) {
                case 'checkConfirmedPay':
                    return $this->parseAnswer($res, $this->getStatusSuccessConfirmedPay());
                case 'checkCancelPay':
                    return $this->parseAnswer($res, $this->getStatusSuccessCancelPay());
                case 'checkHoldPay':
                    return $this->parseAnswer($res, $this->getStatusSuccessHoldMoneyPay());
            }

            return $this->getFail();
        } catch (Exception $e) {
            Logger::dump($e->getMessage());

            return $this->getFail();
        }
    }

    /** Метод проверки статуса */
    public function getStatusPay($paymentId)
    {
        if ((int) $paymentId == false) {
            return $this->getFail(); //Ничего страшного, просто не будет запроса
        }

        $aFields = [
            'TerminalKey' => $this->terminalKey,
            'PaymentId' => $paymentId,
            'Password' => $this->password,
        ];
        $aFields = $this->buildData($aFields);

        return $this->sendRequest($this->getGetStateGateway(), $aFields);
    }

    /**
     * Формирует строку обязательных дополнительных параметров,
     * используется ТОЛЬКО при создании платёжной сессии для DATA.
     */
    public function setData()
    {
        /** @var OrderRow $aOrder */
        $aOrder = Order::find($this->getOrderId());
        $aData = [
            'Email' => $aOrder->mail,
            'Phone' => $aOrder->phone,
        ];

        ksort($aData);

        return http_build_query($aData, '', '|');
    }

    /**
     * Отправляет данные на удаленный шлюз.
     *
     * @param $url string Шлюз
     * @param $aFields
     *
     * @return mixed
     */
    protected function sendRequest($url, $aFields)
    {
        if (is_array($aFields)) {
            $aFields = http_build_query($aFields);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); //16s timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aFields));
        //		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $answer = curl_exec($ch);

        if (curl_errno($ch)) {
            Logger::dump('----ERROR CURL====', curl_error($ch));
        }

        curl_close($ch);

        return $answer;
    }

    /**
     * Сортирует и формирует токен.
     *
     * @param $aFields
     *
     * @return array
     */
    private function buildData($aFields)
    {
        ksort($aFields);
        //Подпись составляется из всех значений
        foreach ($aFields as &$field) {
            trim($field);
        }
        unset($field);

        $token = implode('', $aFields);
        $token = hash('sha256', $token);
        $aFields['Token'] = $token;

        return $aFields;
    }

    /**
     * Отменяет платёж.
     */
    public function cancelPay()
    {
        /** @var OrderRow $oOrder */
        $oOrder = Order::find($this->getOrderId());
        if (!$oOrder) {
            return $this->getFail();
        }

        $aFields = [
            'TerminalKey' => $this->terminalKey,
            'PaymentId' => $oOrder->paymentId,
            'Password' => $this->password,
        ];

        $aFields = $this->buildData($aFields);

        return $this->sendRequest($this->getCancelGateway(), $aFields);
    }

    /**
     * Создание платёжной сессии.
     *
     * @return mixed
     */
    public function initPay()
    {
        $aFields = [
            'TerminalKey' => $this->terminalKey,
            'Amount' => $this->getSum(),
            'OrderId' => $this->getOrderId(),
            'Language' => 'ru',
            'DATA' => $this->setData(),
            'Password' => $this->password,
        ];
        $aFields = $this->buildData($aFields);

        return $this->sendRequest($this->getInitGateway(), $aFields);
    }

    /**
     * Функция разбора ответа и получение результата на основе ожидаемых ответов.
     *
     * @param $answer string|array Полученный ответ от сервера
     * @param $aStatuses array Ожидаемые статусы
     * @param $bOutData bool флаг сохранения результата в переменную $aResData
     *
     * @return mixed
     */
    protected function parseAnswer($answer, $aStatuses, $bOutData = false)
    {
        if (!$answer) {
            return $this->getFail();
        }

        if (!is_array($answer)) {
            $aRes = json_decode($answer, true);
        } else {
            $aRes = $answer;
        }

        if ($bOutData) {
            $this->aResData = $aRes;
        }

        if ($aRes['Success'] == true and $aRes['OrderId'] == $this->getOrderId()) {
            foreach ($aStatuses as $status) {
                if ($status == $aRes['Status']) {
                    return $this->getSuccess();
                }
            }
        }

        return $this->getFail();
    }
}
