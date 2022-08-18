<?php

declare(strict_types=1);

namespace skewer\build\Page\Cart;

use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\build\Adm\Order\ar\GoodsRow as ArGoodsRow;
use skewer\build\Adm\Order\ar\OrderRow;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Adm\Order\Service as OrderService;
use skewer\build\Tool\DeliveryPayment\Api as DeliveryApi;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;
use skewer\build\Tool\Payments\Api as PaymentApi;
use skewer\build\Tool\Payments\Payment;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\models\Users;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsRow;
use skewer\components\forms\Api as FormsApi;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\i18n\ModulesParams;

/**
 * Class OrderForm.
 *
 * @property string $name
 * @property string $postcode
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string $tp_deliv
 * @property string $tp_pay
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class OrderEntity extends BuilderEntity
{
    public $redirectKeyName = 'order';
    public $cmd = 'sendOrder';

    /** @var bool Флаг на быстрый заказ через 1 клик */
    protected $fast = false;
    /** @var int */
    protected $idSection;
    /** @var OrderRow */
    private $orderRow;
    /** @var bool */
    private $ajaxShow;

    public function __construct(
        int $idSection,
        $ajaxShow = false,
        array $innerData = [],
        array $config = []
    ) {
        $this->idSection = $idSection;
        $this->ajaxShow = $ajaxShow;
        $innerData = $this->getUserData($innerData);

        parent::__construct($innerData, $config);

        //необходимо установить переденные параметры для отображения
        $this->setValues();
    }

    public static function tableName(): string
    {
        return 'form_cart_order';
    }

    public static function createTable()
    {
    }

    /**
     * Заполнение формы
     * используя анкетные данные пользователя.
     *
     * @param array $innerData
     *
     * @return array
     */
    public function getUserData(array $innerData = [])
    {
        if (CurrentUser::getId() && CurrentUser::getPolicyId() != Auth::getDefaultGroupId()) {
            /** @var Users $user */
            $user = Users::findOne(CurrentUser::getId());
            $innerData['name'] = $innerData['name'] ?? $user->name;
            $innerData['postcode'] = $innerData['postcode'] ?? $user->postcode;
            $innerData['email'] = $innerData['email'] ?? $user->email;
            $innerData['address'] = $innerData['address'] ?? $user->address;
            $innerData['phone'] = $innerData['phone'] ?? $user->phone;
        }

        return $innerData;
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        if ($this->saveOrderForm() && !$this->isSendCRMData) {
            $this->sendDataToCRM($this->orderRow);
            $this->isSendCRMData = true;
        }

        return true;
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->paramsForInputTemplate = [
            'fastBuy' => $this->fast,
            'ajaxShow' => $this->ajaxShow,
        ];
        $templateForm->paramsForButtonTemplate = [
            'fastBuy' => $this->fast,
            'section_path' => Tree::getSectionAliasPath($this->idSection, true),
        ];
        $templateForm->ajaxForm = $this->ajaxShow;
        $templateForm->tagAction = Tree::getSectionAliasPath(
            $this->idSection,
            true
        ) . 'checkout/';
    }

    /**
     * Форма сохранения заказа из формбилдера.
     *
     * @throws \Exception
     *
     * @return bool| int
     */
    protected function saveOrderForm()
    {
        $this->setOrderRow();

        $orderId = $this->orderRow->save();

        /** Заказ корзины */
        $order = Api::getOrder($this->fast);

        $typeDeliveries = TypeDelivery::findOne(
            ['id' => $this->orderRow->type_delivery]
        );
        if ($typeDeliveries) {
            $order->setTypeDelivery($typeDeliveries->attributes);
        }

        $this->orderRow->price_delivery = $order->getPriceDelivery();
        $this->orderRow->cache_cart = $this->getJsonCacheCart($order);
        $this->orderRow->save();

        $total = 0;

        $goods = $order->getItems();
        // Сохранить товары заказа в БД
        foreach ($goods as &$paOrderItem) {
            $total += $paOrderItem['total'];
            $paOrderItem['price'] = Api::priceFormat($paOrderItem['price']);

            $oGood = GoodsRow::get($paOrderItem['id_goods']);
            $paOrderItem['payment_object'] = $oGood->getPaymentObject();

            $oOrderGood = new ArGoodsRow();
            $oOrderGood->setData($paOrderItem + ['id_order' => $orderId]);
            $oOrderGood->save();

            $paOrderItem['show_detail'] = !Card::isDetailHiddenByCard($oGood->getExtRow()->getModel()->getName());
        }

        $values = $this->orderRow->getData();

        // собираем данные для мыла
        $dataForMailOrder = [];
        $changeFields = [
            'tp_deliv' => 'type_delivery',
            'tp_pay' => 'type_payment',
            'name' => 'person',
            'email' => 'mail',
        ];

        /**
         * @var string
         * @var FieldAggregate $field
         */
        foreach ($this->fields as $fieldName => $field) {
            switch ($fieldName) {
                case 'tp_deliv':
                    $fieldName = $changeFields[$fieldName];
                    $values[$fieldName] = $typeDeliveries->title;
                    break;
                case 'tp_pay':
                    $fieldName = $changeFields[$fieldName];
                    $values[$fieldName] = DeliveryApi::getTitleTypePayment($values[$fieldName]);
                    break;
                case 'name':
                case 'email':
                    $fieldName = $changeFields[$fieldName];
                    break;
            }

            if (isset($values[$fieldName])) {
                $dataForMailOrder[] = [
                    'title' => $field->settings->title,
                    'value' => $values[$fieldName],
                ];
            }
        }

        $goods = $order->getItems();
        foreach ($goods as &$item) {
            $sGoods = GoodsRow::get($item['id_goods']);
            $item['show_detail'] = !Card::isDetailHiddenByCard(
                $sGoods->getExtRow()->getModel()->getName()
            );
        }

        $order->setItems($goods);

        $aOptionsUser = $this->letterForSend($order, $dataForMailOrder);

        // письмо клиенту
        if ($this->orderRow->mail) {
            $sTitle = ModulesParams::getByName('order', 'title_user_mail');
            $sBody = ModulesParams::getByName('order', 'user_content');

            OrderService::sendMail(
                $this->orderRow->mail,
                $sTitle,
                $sBody,
                $aOptionsUser
            );
        }

        $sTitle = ModulesParams::getByName('order', 'title_adm_mail');
        $sBody = ModulesParams::getByName('order', 'adm_content');
        $aOptionsAdm = $this->letterForSend($order, $dataForMailOrder, true);

        /*Если форма купить в 1 клик, пытаемся навесить дополнительный контент результирующей*/
        if ($total > 0) {
            if ($this->orderRow->status == Status::getIdByNew()) {
                /**
                 * @var TypePayment
                 */
                $oPaymentType = TypePayment::findOne(['id' => $this->orderRow->type_payment]);

                if ($oPaymentType && $oPaymentType->payment) {
                    /** @var Payment $oPayment */
                    $oPayment = PaymentApi::make($oPaymentType->payment);

                    if ($oPayment) {
                        $oPayment->setOrderId($this->orderRow->id);
                        $oPayment->setSum($total);

                        FormsApi::$sRedirectUri = Tree::getSectionAliasPath(
                            \Yii::$app->sections->getValue('cart')
                            ) . "done?token={$this->orderRow->token}";
                        FormsApi::$sAnswerText = \Yii::t(
                            'order',
                            'success_1_click'
                        );
                    }
                }
            }
        }

        // письмо админу
        OrderService::sendMail(
            Site::getAdminEmail(),
            $sTitle,
            $sBody,
            $aOptionsAdm
        );

        Api::clearOrder($this->fast);

        return $orderId;
    }

    /**
     * установка данных о заказе.
     */
    private function setOrderRow()
    {
        $this->orderRow = new OrderRow();
        $this->orderRow->address = $this->address;
        $this->orderRow->person = $this->name;
        $this->orderRow->phone = $this->phone;
        $this->orderRow->mail = $this->email;
        $this->orderRow->postcode = $this->postcode;
        $this->orderRow->text = $this->getField('text') ? $this->getField('text')->value : '';
        $this->orderRow->type_delivery = $this->getInnerParamByName('tp_deliv');
        $this->orderRow->type_payment = $this->getInnerParamByName('tp_pay');
        $this->orderRow->is_mobile = $this->getInnerParamByName('is_mobile');

        $dNow = new \DateTime('NOW');
        $this->orderRow->date = $dNow->format('Y-m-d H:i:s');
        $this->orderRow->status = Status::getIdByNew();

        // готовим токен
        $salt = '#*#BaraNeKontritsya322#*#';
        $sha512 = hash('sha512', random_int(0, 1000) . $salt . $this->orderRow->date);
        $this->orderRow->token = $sha512;

        // если авторизован, сохраняем id клиента
        if (CurrentUser::isLoggedIn()) {
            $this->orderRow->auth = CurrentUser::getId();
        }
    }

    private function getJsonCacheCart(Order $order): string
    {
        return json_encode([
            'paid_delivery' => SysVar::get(DeliveryApi::PAID_DELIVERY),
            'free_shipping' => $order->getFreeShipping(),
            'coord_deliv_costs' => $order->getCoordDelivCosts(),
            'hide_price_fractional' => SysVar::get(
                'catalog.hide_price_fractional'
            ),
        ]);
    }

    /**
     * Отправка письма.
     *
     * @param $oOrderCart
     * @param $dataForMail
     * @param bool $admin
     *
     * @return array
     */
    private function letterForSend(
        Order $oOrderCart,
        array $dataForMail,
        $admin = false
    ) {
        $aDataSend = [
            'date' => $this->orderRow->date,
            'orderId' => $this->orderRow->id,
        ];

        if (($admin && !$this->formAggregate->settings->noSendDataInLetter) || !$admin) {
            $orderTemplate = Parser::parseTwig(
                'mail.twig',
                $aDataSend + [
                    'totalPrice' => $oOrderCart->getTotalPrice(),
                    'paidDelivery' => $oOrderCart->getPaidDelivery(),
                    'deliveryPrice' => $oOrderCart->getDeliveryForPage(),
                    'currency' => is_numeric($oOrderCart->getDeliveryForPage()),
                    'totalPriceToPay' => $oOrderCart->getTotalPriceToPay(),
                    'aGoods' => $oOrderCart->getItems(),
                    'items' => $dataForMail,
                    'webrootpath' => Site::httpDomain(),
                    'isArticle' => Api::isArticle(),
                ],
                RELEASEPATH . 'build/Adm/Order/templates/'
            );
        } else {
            $orderTemplate = Parser::parseTwig(
                'dateAndNumberOrder.twig',
                $aDataSend,
                RELEASEPATH . 'build/Adm/Order/templates/'
            );
        }
        $options = [
            'order_id' => $this->orderRow->id,
            'token' => $this->orderRow->token,
            'order_info' => $orderTemplate,
        ];

        return $options;
    }

    public function getToken(): string
    {
        return $this->orderRow->token;
    }

    public function getIdOrderRow(): int
    {
        return $this->orderRow->id;
    }

    /**
     * Одинаковый набор перекрываемых шаблонов для форм
     * (для button и для input).
     *
     * @param string $shortNameTemplate
     *
     * @return string
     */
    public function getNameClassForTemplate(string $shortNameTemplate): string
    {
        return 'OrderEntity' . ucfirst($shortNameTemplate);
    }
}
