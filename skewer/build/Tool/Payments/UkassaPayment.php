<?php

namespace skewer\build\Tool\Payments;

use skewer\base\log\Logger;
use skewer\base\orm\ActiveRecord;
use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\build\Adm\Order as AdmOrder;
use skewer\build\Adm\Order\ar\Goods;
use skewer\build\Adm\Order\ar\Order;
use YandexCheckout\Client;
use YandexCheckout\Common\Exceptions\ApiException;
use YandexCheckout\Common\Exceptions\BadApiRequestException;
use YandexCheckout\Common\Exceptions\ForbiddenException;
use YandexCheckout\Common\Exceptions\InternalServerError;
use YandexCheckout\Common\Exceptions\NotFoundException;
use YandexCheckout\Common\Exceptions\ResponseProcessingException;
use YandexCheckout\Common\Exceptions\TooManyRequestsException;
use YandexCheckout\Common\Exceptions\UnauthorizedException;
use YandexCheckout\Request\Payments\CreatePaymentResponse;
use Yii;
use yii\base\ExitException;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class UkassaPayment extends Payment
{
    /** @var string Текстовый тип системы оплаты */
    const PAYMENT_TYPE = 'ukassa';
    /** @var string Поле заказа для хранения id платежа */
    const ORDER_FIELD = 'ukassa_payment_id';
    /** @var string Статус отмененного заказа от сервера юкассы */
    const CANCELED_STATUS = 'canceled';
    /** @var [] Список полей для редактирования */
    protected static $aFields = [
        ['shopId', 'ukassa_shopId_field', 'i', 'str'],
        ['secret_key', 'ukassa_secret_key_field', 's', 'pass'],
        ['tax', 'ukassa_tax_field', 's', 'select', [
            1 => 'tax_none',
            2 => 'tax_vat0',
            3 => 'tax_vat10',
            4 => 'tax_vat20',
            5 => 'tax_vat110',
            6 => 'tax_vat120'
        ]]
    ];
    /** @var string Секретный ключ магазина */
    protected $sShopSecretKey = '';
    /** @var Client Соединение с яндекс кассой */
    protected $oClient;
    /** @var int Идентификатор Контрагента */
    protected $sShopId = 0;
    /** @var int Код ставки НДС */
    protected $iTaxCode;
    /** @var string Сообщение об успешной оплате */
    private $sSuccessMsg = 'OK';
    /** @var string Сообщение об неуспешной оплате */
    private $sFailMsg = 'FAIL';

    /**
     * Инициализация параметров
     * @param [] $aParams
     * @return bool
     */
    public function initParams($aParams = []): bool
    {
        $this->active = ArrayHelper::getValue($aParams, 'active', false);
        $this->sShopId = ArrayHelper::getValue($aParams, 'shopId');
        $this->sShopSecretKey = ArrayHelper::getValue($aParams, 'secret_key');
        $this->iTaxCode = ArrayHelper::getValue($aParams, 'tax');

        if (empty($this->sShopId) || empty($this->sShopSecretKey)) {
            $this->bInitParams = false;
            Logger::error('ЮКасса: Отсутствуют параметры подключения.');
        } else {
            $this->oClient = new Client();
            $this->oClient->setAuth($this->sShopId, $this->sShopSecretKey);
        }

        return $this->bInitParams;
    }

    /**
     * Получение строкового представления типа платежа
     *
     * @return string
     */
    public function getType(): string
    {
        return self::PAYMENT_TYPE;
    }

    /**
     * @return bool|null
     * @throws ExitException
     */
    public function checkResult(): bool
    {
        $sSource = file_get_contents('php://input');
        $aRequestBody = json_decode($sSource, true);
        $sEventType = $aRequestBody['event'];
        $aPayment = $aRequestBody['object'];

        $sPaymentId = $aPayment['id'];
        if (empty($sPaymentId)) {
            Logger::error('ЮКасса: ошибка проверки оплаты - отсутствует идентификатор платежа.');
            Yii::$app->response->setStatusCode(400)->send();
            Yii::$app->end();
        }

        $bPaid = $aPayment['paid'];
        $this->setOrderId($this->getOrderByPaymentId($sPaymentId));
        $this->setSum($aPayment['amount']['value']);

        $mResult = ($bPaid && $sEventType === 'payment.succeeded');
        if (!$mResult) {
            if ($sEventType !== 'payment.cancelled') {
                $mResult = null;
            }
        }

        return $mResult;
    }

    /**
     * Получение id заказа по id платежа.
     *
     * @param $sPaymentId string ID платежа
     * @return int
     * @throws ExitException
     */
    private function getOrderByPaymentId(string $sPaymentId): int
    {
        $aOrder = (new Query())
            ->from(AdmOrder\ar\Order::getTableName())
            ->select('id')
            ->where([self::ORDER_FIELD => $sPaymentId])
            ->one();

        if (empty($aOrder)) {
            Yii::$app->response->setStatusCode(404)->send();
            Yii::$app->end();
        }

        return $aOrder['id'];
    }

    /**
     * Вывод формы для оплаты
     * @return string
     */
    public function getForm(): string
    {
        if (!$this->active) {
            return '';
        }

        try {
            $aOrderData = $this->getOrderData();
            $iOrderId = $this->getOrderId();

            if (!empty($aOrderData[self::ORDER_FIELD])) {
                $sPaymentId = $aOrderData[self::ORDER_FIELD];
                $oPaymentResponse = $this->oClient->getPaymentInfo($sPaymentId);
                if ($oPaymentResponse->getStatus() === self::CANCELED_STATUS) {
                    $oPaymentResponse = $this->recreatePaymentForOrder($iOrderId);
                }
            } else {
                $oPaymentResponse = $this->recreatePaymentForOrder($iOrderId);
            }
            $sStatus = $oPaymentResponse->getStatus();

            $oPaymentConfirmation = $oPaymentResponse->getConfirmation();
            $sConfirmationUrl = '';
            if (!empty($oPaymentConfirmation)) {
                $sConfirmationUrl = $oPaymentConfirmation->getConfirmationUrl();
            }

            return Parser::parseTwig('payment_form.twig', [
                'status' => $sStatus,
                'paymentLink' => $sConfirmationUrl
            ], BUILDPATH . '/Tool/Payments/templates/');
        } catch (\Exception $e) {
            Logger::error("ЮКасса: ошибка создания формы платежа.");
            Logger::error($e);

            return Parser::parseTwig('payment_creation_error.twig', [], BUILDPATH . '/Tool/Payments/templates/');
        }
    }

    /**
     * @return array|bool|ActiveRecord
     */
    private function getOrderData()
    {
        return Order::find()
            ->where('id', $this->getOrderId())
            ->asArray()
            ->getOne();
    }

    /**
     * Создание объекта платежа
     *
     * @param int $iOrderId
     * @return CreatePaymentResponse|null
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    protected function createPayment(int $iOrderId): CreatePaymentResponse
    {
        return $this->oClient->createPayment(
            [
                'amount' => [
                    'value' => $this->getSum(),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => $this->getReturnUrl(),
                ],
                'receipt' => $this->getReceipt(),
                'capture' => true,
                'description' => 'Заказ №' . $iOrderId,
            ],
            uniqid('', true)
        );
    }

    /**
     * Сумма заказа
     * @return float|string
     */
    public function getSum()
    {
        return number_format($this->sum, 2, '.', '');
    }

    /**
     * Получение URL возврата после оплаты.
     *
     * @return string
     */
    protected function getReturnUrl(): string
    {
        $sOrderDetail = Tree::getSectionAliasPath(Yii::$app->sections->getValue('profile'));
        $oOrder = $this->getCurrentOrder();
        return Site::httpDomain() . $sOrderDetail . '/detail?token=' . $oOrder->token;
    }

    /**
     * Получение данных для чека
     *
     * @return array
     */
    private function getReceipt(): array
    {
        $aOrderData = $this->getOrderData();
        $aReceipt = [
            'customer' => [
                'phone' => $this->preparePhoneNumber($aOrderData['phone'])
            ]
        ];

        $aOrderGoods = Goods::find()
            ->where('id_order', $this->getOrderId())
            ->asArray()
            ->getAll();

        foreach ($aOrderGoods as $aGoods) {
            $aReceipt['items'][] = [
                'description' => $aGoods['title'],
                'quantity' => $aGoods['count'],
                'amount' => [
                    'value' => $aGoods['price'],
                    'currency' => 'RUB'
                ],
                'vat_code' => $this->iTaxCode
            ];
        }

        if ($aOrderData['price_delivery']) {
            $aReceipt['items'][] = [
                'description' => Yii::t('payments', 'delivery_title'),
                'quantity' => 1,
                'amount' => [
                    'value' => $aOrderData['price_delivery'],
                    'currency' => 'RUB'
                ],
                'vat_code' => $this->iTaxCode
            ];
        }

        return $aReceipt;
    }

    /**
     * @param string $sPhone
     * @return string
     */
    private function preparePhoneNumber(string $sPhone): string
    {
        return preg_replace('/[^0-9]/', '', $sPhone);
    }

    /**
     * @param $sPaymentId
     * @param $iOrderId
     */
    private function updateOrderPaymentId($sPaymentId, $iOrderId)
    {
        Order::update()
            ->set(self::ORDER_FIELD, $sPaymentId)
            ->where('id', $iOrderId)
            ->get();
    }

    /**
     * Сообщение о неуспешной оплате.
     *
     * @return string
     */
    public function getFail(): string
    {
        return $this->sFailMsg;
    }

    /**
     * Сообщение об успешной оплате.
     *
     * @return string
     */
    public function getSuccess(): string
    {
        return $this->sSuccessMsg;
    }

    /**
     * @return array|bool|ActiveRecord
     */
    protected function getCurrentOrder()
    {
        return Order::findOne(['id' => $this->orderId]);
    }

    /**
     * @param int $iOrderId
     * @return \YandexCheckout\Request\Payments\CreatePaymentResponse|null
     * @throws \YandexCheckout\Common\Exceptions\ApiException
     * @throws \YandexCheckout\Common\Exceptions\BadApiRequestException
     * @throws \YandexCheckout\Common\Exceptions\ForbiddenException
     * @throws \YandexCheckout\Common\Exceptions\InternalServerError
     * @throws \YandexCheckout\Common\Exceptions\NotFoundException
     * @throws \YandexCheckout\Common\Exceptions\ResponseProcessingException
     * @throws \YandexCheckout\Common\Exceptions\TooManyRequestsException
     * @throws \YandexCheckout\Common\Exceptions\UnauthorizedException
     */
    protected function recreatePaymentForOrder(int $iOrderId)
    {
        $oPaymentResponse = $this->createPayment($iOrderId);
        $sPaymentId = $oPaymentResponse->getId();
        $this->updateOrderPaymentId($sPaymentId, $iOrderId);

        return $oPaymentResponse;
    }
}