<?php

namespace skewer\build\Tool\Payments;

use skewer\base\site\Site;
use skewer\base\site_module\Parser;

/**
 * Class Payments.
 */
abstract class Payment
{
    protected static $instances = [];

    /** @var bool Активность */
    protected $active = false;

    /**
     * @var bool Тестовый режим
     * <br /> 1/true - тестовый режим
     * <br /> 0/false - боевой режим - реальные деньги списываются
     */
    protected $test_life = true;

    /** @var int Идентификатор заказа */
    protected $orderId = 0;

    /** @var float Сумма заказа */
    protected $sum = 0;

    /** @var string Описание заказа */
    protected $description;

    /** @var bool Флаг инициализации параметров */
    protected $bInitParams = false;

    /**
     * @var array Список полей для редактирования
     * Формат [ 'name', 'title', 'type', 'field_type' ]
     */
    protected static $aFields = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
            if (method_exists(self::$instances[$class], 'init')) {
                self::$instances[$class]->init();
            }
        }

        return self::$instances[$class];
    }

    /**
     * Пользовательская функция инициализации.
     */
    public function init()
    {
    }

    /**
     * Отдает флаг инициализации параметров.
     *
     * @return bool
     */
    public function isInitParams()
    {
        return $this->bInitParams;
    }

    /**
     * Тип агрегатора систем оплат
     *
     * @return mixed
     */
    abstract public function getType();

    /**
     * Метод проверки результата об оплате, полученного от системы платежей
     * * Возвращаемые значения: Если true то заказ оплачен. false -- заказ отменён. Иначе не менять статус заказа.
     *
     * @return bool|mixed
     */
    abstract public function checkResult();

    /**
     * Вывод формы для оплаты.
     *
     * @return string
     */
    abstract public function getForm();

    /**
     * Функция определения того, что код, отданный методом getForm() является
     * валидной формой.
     *
     * @param $sForm
     *
     * @return bool
     */
    public function isValidForm($sForm)
    {
        if (!$sForm) {
            return false;
        }

        return mb_strpos($sForm, '<form') !== false;
    }

    /**
     * Сообщение об успешной оплате.
     *
     * @return string
     */
    abstract public function getSuccess();

    /**
     * Сообщение о неуспешной оплате.
     *
     * @return string
     */
    abstract public function getFail();

    /**
     * Инициализация параметров.
     *
     * @param [] $aParams
     */
    abstract public function initParams($aParams = []);

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
                        $aField[4]['show_val'][$iKey] = \Yii::t('payments', $sName);
                    }
                }
                $aFields[] = $aField;
            }
        }

        return $aFields;
    }

    /**
     * Возвращает номер заказа.
     *
     * @return int Номер заказа
     */
    public function getOrderId()
    {
        return (int) $this->orderId;
    }

    /**
     * Устанавливает номер заказа.
     *
     * @param int $orderId Номер заказа
     */
    public function setOrderId($orderId)
    {
        $this->orderId = (int) $orderId;
    }

    /**
     * Возвращает сумму заказа.
     *
     * @return float Сумма заказа
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Устанавливает сумму заказа.
     *
     * @param float $sum Сумма заказа
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
    }

    /**
     * Возвращает описание заказа.
     *
     * @return string Описание заказа
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Устанавливает описание заказа.
     *
     * @param string $description Описание заказа
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Активность.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Установка активности.
     *
     * @param $active
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    /**
     * Парсинг формы.
     *
     * @param Form $oForm
     *
     * @return string
     */
    final protected function parseForm(Form $oForm)
    {
        return Parser::parseTwig('form.twig', ['Form' => $oForm], BUILDPATH . '/Tool/Payments/templates/');
    }

    /**
     * Урл успешной отправки.
     *
     * @return string
     */
    protected function getSuccessUrl()
    {
        return Site::httpDomain() . \Yii::$app->router->rewriteURL('[' . \Yii::$app->sections->getValue('payment_success') . ']') . '?order_id=' . $this->orderId;
    }

    /**
     * Урл отмены оплаты.
     *
     * @return string
     */
    protected function getCancelUrl()
    {
        return Site::httpDomain() . \Yii::$app->router->rewriteURL('[' . \Yii::$app->sections->getValue('payment_fail') . ']') . '?order_id=' . $this->orderId;
    }

    /**
     * Метод выполняемый после прохождения оплаты.
     *
     * @param mixed $sResult
     */
    public function afterPayment($sResult)
    {
    }

    protected function getPaymentMethod()
    {
        $sValue = Api::getPaymentMethod();

        if (empty($sValue)) {
            $sValue = Api::DEFAULT_PAYMENT_METHOD;
        }
        $aPaymentMethods = $this->getPaymentMethods();

        return $aPaymentMethods[$sValue];
    }

    /**
     * Ключ в массиве, значения признака объекта расчета,
     * полное соответствие Api::$aPaymentObject
     * Значения массива - это параметры принимаемые платежной системой
     * соспосталенные с теми, что в CMS
     * Если не совпадают, то переопределить функцию в конкретном классе.
     *
     * @return array
     */
    protected function getPaymentObjects()
    {
        return [
            'commodity' => 'commodity',
            'excise' => 'excise',
            'job' => 'job',
            'service' => 'service',
            'gambling_bet' => 'gambling_bet',
            'gambling_prize' => 'gambling_prize',
            'lottery' => 'lottery',
            'lottery_prize' => 'lottery_prize',
            'intellectual_activity' => 'intellectual_activity',
            'payment' => 'payment',
            'agent_commission' => 'agent_commission',
            'composite' => 'composite',
            'another' => 'another',
            'property_right' => 'property_right',
            'non-operating_gain' => 'non-operating_gain',
            'insurance_premium' => 'insurance_premium',
            'sales_tax' => 'sales_tax',
            'resort_fee' => 'resort_fee',
        ];
    }

    /**
     * Ключ в массиве, значения признака объекта расчета,
     * полное соответствие Api::$aPaymentMethod
     * Значения массива - это параметры принимаемые платежной системой
     * соспосталенные с теми, что в CMS
     * Если не совпадают, то переопределить функцию в конкретном классе.
     *
     * @return array
     */
    protected function getPaymentMethods()
    {
        return [
            'full_prepayment' => 'full_prepayment',
            'prepayment' => 'prepayment',
            'advance' => 'advance',
            'full_payment' => 'full_payment',
            'partial_payment' => 'partial_payment',
            'credit' => 'credit',
            'credit_payment' => 'credit_payment',
        ];
    }

    /**
     * @param array $aGood
     *
     * @return string
     */
    protected function getPaymentObject($aGood)
    {
        if (!isset($aGood['payment_object']) || empty($aGood['payment_object'])) {
            $aGood['payment_object'] = Api::DEFAULT_PAYMENT_OBJECT;
        }

        $aPaymentObjects = $this->getPaymentObjects();

        if (isset($aPaymentObjects[$aGood['payment_object']])) {
            return $aPaymentObjects[$aGood['payment_object']];
        }

        return $aPaymentObjects[Api::DEFAULT_PAYMENT_OBJECT];
    }
}
