<?php

namespace skewer\build\Page\Cart;

use skewer\base\orm\Query;
use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Adm\Order as AdmOrder;
use skewer\build\Page\CatalogViewer;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;
use skewer\build\Tool\Payments as Payments;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\GoodsSelector;
use skewer\components\ecommerce;
use skewer\components\forms\Api as ApiForms;
use skewer\components\forms\FormBuilder;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    /** @var int ID текущего раздела */
    public $sectionId;

    public $bIsFastBuy = false;

    /** @var string имя параметра для команды */
    private $sActionParamName = 'action';

    /**
     * Шаблон вывода списка.
     *
     * @var string
     */
    public $template = 'list.twig';

    private $bHaveUnavalGoods = false;

    /** @var string шаблон формы оформления заказа */
    public $checkoutTemplate = 'checkout.twig';

    /**
     * Инициализация.
     */
    public function init()
    {
        $this->sectionId = $this->sectionId();
    }

    protected function getActionParamName()
    {
        return $this->sActionParamName;
    }

    public function getBaseActionName()
    {
        return 'list';
    }

    /**
     * Запуск модуля.
     *
     * @return int
     */
    public function execute()
    {
        $this->executeRequestCmd();

        $this->bIsFastBuy = $this->getStr('ajaxShow', 0);

        if ($this->getStr('cmd', '') && ($this->bIsFastBuy || $this->getStr('fastBuy'))) {
            $this->sActionParamName = 'cmd';
        }

        return parent::execute();
    }

    /**
     * Список позиций заказа.
     *
     * @return int
     */
    public function actionList()
    {
        // подключен для js и css для кнопок "+" и "-"
        CatalogViewer\Asset::register(\Yii::$app->getView());

        $oOrder = Api::getOrder();

        $oOrder = $this->validateGoods($oOrder);

        $aItems = $oOrder->getItems();

        if (\skewer\components\catalog\Api::isIECommerce()) {
            $aItems = ecommerce\Api::addEcommerceDataInGoodsOrder($aItems);
        }

        foreach ($aItems as &$aObject) {
            $aObject['show_detail'] = (int) !Card::isDetailHiddenByCard($aObject['card']);
        }

        $oOrder->setItems($aItems);
        $this->setData('protect', SysVar::get('Page.not_save_image_fancybox', 0));
        $this->setData('transitionEffect', SysVar::get('Page.image_change_effect', 'disable'));
        $this->setTemplate($this->template);
        $this->setData('order', $oOrder);
        $this->setData('sectionId', $this->sectionId);
        $this->setData('mainSection', \Yii::$app->sections->main());
        $this->setData('isArticle', Api::isArticle());

        return psComplete;
    }

    /**
     * Форма оформления заказа.
     *
     * @throws NotFoundHttpException
     *
     * @return int
     */
    public function actionCheckout()
    {
        // Заказ в один клик?
        if ($this->bIsFastBuy) {
            $this->sectionId = \Yii::$app->sections->getValue('cart');

            $oOrderCart = Api::getOrder($this->bIsFastBuy);
            $oOrderCart->unsetAll();
            Api::setOrder($oOrderCart, true);

            $iObj = $this->getInt('idObj');

            if (!$iObj) {
                return 0;
            }
            $count = $this->getInt('count', 1);
            if (!$count) {
                return 0;
            }
            Api::setItem($iObj, $count, true);
            $this->setData('fastBuy', $this->bIsFastBuy);
            $this->setData('isArticle', Api::isArticle());
        }

        /** @var Order $oOrderCart */
        $oOrderCart = Api::getOrder($this->bIsFastBuy);
        $ajaxForm = $this->getInt('ajaxForm');

        // Не выводим форму, если заказ пустой
        if (!$oOrderCart->getCount()) {
            throw new NotFoundHttpException();
        }
        /*Проверим доступность товаров которые лежат в админке*/
        $oOrderCart = $this->validateGoods($oOrderCart);

        $this->setTemplate($this->checkoutTemplate);
        $this->setData('order', $oOrderCart);
        $this->setData('isArticle', Api::isArticle());
        $this->setData('totalPrice', $oOrderCart->getTotalPrice());
        $this->setData('paidDelivery', $oOrderCart->getPaidDelivery());
        if ($oOrderCart->getPaidDelivery()) {
            $this->setData('priceDelivery', $oOrderCart->getDeliveryForPage());
            $this->setData('currenty', !is_numeric($oOrderCart->getDeliveryForPage()));
            $this->setData('totalPriceToPay', $oOrderCart->getTotalPriceToPay());
        }

        $sCmd = $this->get('cmd');
        $aData = $this->getPost();
        if (\Yii::$app->session->get('CartFormContainer') !== null && !$aData) {
            $aData = \Yii::$app->session->get('CartFormContainer');
            \Yii::$app->session->set('CartFormContainer', null);

            //сброс значение, чтобы подтягивались дефолтные
            if (isset($aData['tp_deliv']) && $aData['tp_deliv']) {
                unset($aData['tp_deliv']);
            }
            if (isset($aData['tp_pay']) && $aData['tp_pay']) {
                unset($aData['tp_pay']);
            }
        }

        $orderEntity = $this->getEntity($this->bIsFastBuy);

        $formBuilder = new FormBuilder(
            $orderEntity,
            $this->sectionId(),
            $this->get('label')
        );

        switch ($sCmd) {
            case 'sendOrder':

                    /*Если какие то товары стали неактивными уйдем в предыдущее состояние*/
                    if ($this->bHaveUnavalGoods) {
                        /*Кладем пришедшие с формы данные во временный контейнер*/
                        \Yii::$app->session->set('CartFormContainer', $aData);
                        \Yii::$app->response->redirect('/cart');
                        \Yii::$app->end();
                    }

                if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
                    $token = $orderEntity->getToken();

                    $sCanapeuuid = $aData['_canapeuuid'] ?? '';

                    // Разрешаем отправлять ecommerce данные
                    \Yii::$app->session->setFlash('ecommerceSend', true);

                    \Yii::$app->getResponse()->redirect(\Yii::$app->router->rewriteURL("[{$this->sectionId}][Cart?action=done&token={$token}&canapeuuid={$sCanapeuuid}]"), '301')->send();
                    break;
                }

                    $this->setData(
                        'form',
                        $formBuilder->getFormTemplate()
                    );
                    break;
                default:

                    /*Если какие то товары стали неактивными уйдем в предыдущее состояние*/
                    if ($this->bHaveUnavalGoods) {
                        \Yii::$app->response->redirect('/cart');
                        \Yii::$app->end();
                    }

                    /*Если в контейнере есть данные которые пытались ввести в форму, подтянем их*/
                   /* if (!is_null(\Yii::$app->session->get('CartFormContainer'))) {
                        $aData = \Yii::$app->session->get('CartFormContainer');
                        \Yii::$app->session->set('CartFormContainer',null);
                    }*/

                    $this->setData(
                        'form',
                        $formBuilder->getFormTemplate()
                    );

                    $sTitle = \Yii::t('order', 'title_checkout');
                    site\Page::setTitle($sTitle);
                    site\Page::setAddPathItem($sTitle, Api::getUrlCheckoutPage($this->sectionId()));
                    break;
            }

        return psComplete;
    }

    public function actionSendFormOneClick()
    {
        $ajaxForm = $this->getInt('fastBuy');
        $oOrderCart = Api::getOrder($ajaxForm);
        if (!$oOrderCart->getItems()) {
            \Yii::$app->getResponse()->redirect('/');
            \Yii::$app->end();
        }

        $orderEntity = $this->getEntity($ajaxForm);
        $this->sectionId = $this->getInt('section');
        $formBuilder = new FormBuilder(
            $orderEntity,
            $this->sectionId,
            $this->get('label')
        );

        if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
            $formBuilder->setLegalRedirect();
            $ajaxFormSend = $this->get('ajax');

            if (!$ajaxFormSend) {
                $this->setData('form_anchor', true);
            }
            $sAnswer = $formBuilder->buildSuccessAnswer(
                $ajaxFormSend,
                $this->sectionId,
                ['form_section' => $this->sectionId]
            );

            if (ApiForms::$sRedirectUri !== null && ApiForms::$sAnswerText !== null) {
                $sAnswer = ApiForms::$sAnswerText;
                $this->setData('redirect_uri', ApiForms::$sRedirectUri);
            }

            $this->setData('answer', $sAnswer);
            $this->setTemplate('answer.twig');
            $oOrderCart = Api::getOrder($ajaxForm);
            $oOrderCart->unsetAll();
            Api::setOrder($oOrderCart, true);
        } else {
            $this->setData('form', $formBuilder->getFormTemplate());
            $this->setTemplate('form.twig');
        }

        return psComplete;
    }

    /**
     * Завершение заказа.
     *
     * @return int
     */
    public function actionDone()
    {
        // rename title and pathline
        $sTitle = \Yii::t('order', 'title_checkout');

        site\Page::setTitle($sTitle);

        site\Page::setAddPathItem($sTitle, Api::getUrlDonePage($this->sectionId()));

        $sToken = $this->get('token', '');

        if ($sToken) {
            $aOrder = AdmOrder\ar\Order::find()->where('token', $sToken)->order('id', 'DESC')->asArray()->getOne();

            if ($aOrder) {
                $aGoods = AdmOrder\ar\Goods::find()->where('id_order', $aOrder['id'])->asArray()->getAll();

                $total = 0;

                foreach ($aGoods as $aItem) {
                    $total += $aItem['total'];
                }

                if ($total > 0) {
                    if ($aOrder['status'] == AdmOrder\model\Status::getIdByNew()) {
                        if (\skewer\components\catalog\Api::isIECommerce()) {
                            // Отправляем e-commerce данные только один раз
                            if (\Yii::$app->session->getFlash('ecommerceSend', false)) {
                                $this->setData('ecommerce', ecommerce\Api::buildScriptPurchase($aOrder['id']));
                            }
                        }

                        /**
                         * @var TypePayment
                         */
                        $oPaymentType = TypePayment::findOne(['id' => $aOrder['type_payment']]);

                        if ($oPaymentType && $oPaymentType->payment) {
                            /**
                             * @var Payments\Payment
                             */
                            $oPayment = Payments\Api::make($oPaymentType->payment);

                            if ($oPayment) {
                                $oPayment->setOrderId($aOrder['id']);
                                $sum = $total + $aOrder['price_delivery'];
                                $oPayment->setSum($sum);

                                $sForm = $oPayment->getForm();
                                $this->setData('can_be_paid', $oPayment->isValidForm($sForm));
                                $this->setData('paymentForm', $sForm);
                            }
                        }
                    }
                }
            }
        }

        $this->setTemplate('done.twig');
        $this->setData('mainSection', \Yii::$app->sections->main());

        return psComplete;
    }

    /**
     * Добавление новой позиции в заказ.
     */
    public function cmdSetItem()
    {
        $objectId = $this->getInt('objectId');
        $count = $this->getInt('count');

        $bErrorCount = false;

        $sGoods = GoodsRow::get($objectId);

        if ($count and ($count > 0)) {
            $bErrorCount = !Api::setItem($objectId, $count, false);
        }

        if ($sGoods) {
            $aFields = $sGoods->getData();
            $sTitle = $aFields['title'];
        } else {
            $sTitle = '';
        }
        $cartSectionLink = Tree::getSectionAliasPath(\Yii::$app->sections->getValue('cart'));

        $sTemplate = site_module\Parser::parseTwig('fancy_panel.twig', ['cartSectionLink' => $cartSectionLink, 'errorCount' => $bErrorCount, 'title' => $sTitle], BUILDPATH . 'Page/Cart/templates/');

        Api::sendJSON(
            false,
            ['sTemplate' => $sTemplate]
        );
    }

    /**
     * Удаление позиции заказа.
     */
    public function cmdRemoveItem()
    {
        $id = $this->getInt('id');
        $oOrderCart = Api::getOrder();
        $oOrderCart->unsetItem($id);

        Api::setOrder($oOrderCart);
        Api::sendJSON();
    }

    /**
     * Пересчет количества позиции.
     */
    public function cmdRecountItem()
    {
        $id = $this->getInt('id');
        $count = $this->getInt('count');

        if (!$count || $count < 0) {
            exit;
        }

        $oOrderCart = Api::getOrder();

        if ($aItem = $oOrderCart->getItemById($id)) {
            $aGood = GoodsSelector::get($id);

            if ($aGood) {
                // Актуализируем цену товара в заказе
                $aItem['price'] = ArrayHelper::getValue($aGood, 'fields.price.value', '');
            }

            $aItem['count'] = $count;
            $aItem['total'] = $count * Api::priceFormat($aItem['price']);
            $oOrderCart->setItem($aItem);
            Api::setOrder($oOrderCart);
        }

        Api::sendJSON();
    }

    /**
     * Удаление всех позиций.
     */
    public function cmdUnsetAll()
    {
        $oOrderCart = Api::getOrder();
        $oOrderCart->unsetAll();

        Api::setOrder($oOrderCart);
        Api::sendJSON();
    }

    public function cmdUpdateTypeDelivery()
    {
        $iIdDelivery = $this->getInt('id');
        $bFast = (bool) $this->getInt('fastBuy', false);

        $oTypeDelivery = TypeDelivery::findOne(['id' => $iIdDelivery]);

        //обновляем список типов оплат под выбранный тип доставки
        $aTypePayment = $oTypeDelivery->getDeliveryPaymentAsArray(true);
        $data['payments']['payment'] = $aTypePayment;
        $typePayment = current($aTypePayment);
        $oTypePayment = TypePayment::findOne(['id' => $typePayment['id']]);
        $data['payments']['message'] = $oTypePayment->message;

        //пересчитываем стоимость доставки и общую сумму к оплате
        $oOrderCart = Api::getOrder($bFast);
        $oOrderCart->setTypeDelivery($oTypeDelivery->attributes);
        if ($oOrderCart->getPaidDelivery()) {
            $data['delivery'] = [
                'total_price_to_pay' => $oOrderCart->getTotalPriceToPay(),
                'price_delivery' => $oOrderCart->getDeliveryForPage(),
                'address' => $oTypeDelivery->address,
            ];
        }

        echo json_encode(
            $data
        );
        exit;
    }

    public function cmdGetMessage()
    {
        $iIdPayment = $this->getInt('idTypePayment');
        $iIdDelivery = $this->getInt('idTypeDelivery');

        $data = [];

        if ($iIdPayment) {
            $oTypePayment = TypePayment::findOne(['id' => $iIdPayment]);
            $data['messagePayment'] = $oTypePayment->message;
        }

        if ($iIdDelivery) {
            $oTypeDelivery = TypeDelivery::findOne(['id' => $iIdDelivery]);
            $data['messageDelivery'] = $oTypeDelivery->address;
        }

        echo json_encode($data);
        exit;
    }

    /**
     * Проверяет что все товары лежащие в корзине активны и существуют
     *
     * @param Order $oOrderCart
     *
     * @return mixed
     */
    private function validateGoods($oOrderCart)
    {
        $aItems = $oOrderCart->getItems();

        $aGoodsIds = ArrayHelper::getColumn($aItems, 'id_goods');

        if (!$aGoodsIds) {
            return $oOrderCart;
        }

        /*Попытаемся достать товары с такими Id при условии что они активны*/
        $aGoods = Query::SelectFrom('co_base_card')
            ->fields('id,price')
            ->where('id', $aGoodsIds)
            ->where('active', 1)
            ->index('id')
            ->asArray()
            ->getAll();

        $aExistsGoodsIds = ArrayHelper::getColumn($aGoods, 'id');

        foreach ($aItems as &$item) {
            if (!in_array($item['id_goods'], $aExistsGoodsIds)) {
                $this->bHaveUnavalGoods = true;
                $item['not_available'] = 1;
                $item['image'] = '';
            } else {
                $item['not_available'] = 0;
            }

            if (($item['not_available'] == 1) || ($item['price'] != $aGoods[$item['id_goods']]['price'])) {
                $this->bHaveUnavalGoods = true;
                $item['not_actual_price'] = 1;
            } else {
                $item['not_actual_price'] = 0;
            }
        }

        $oOrderCart->setItems($aItems);

        return $oOrderCart;
    }

    /**
     * Форма оформления заказа в 1 коик.
     *
     * @throws NotFoundHttpException
     *
     * @return int
     */
    public function actionCheckoutOneClick()
    {
        $this->bIsFastBuy = 1;
        $this->set('ajax', 0);
        $this->actionCheckout();
    }

    /**
     * Получение entity либо в один клик, либо просто заказ.
     *
     * @param $isFastBuy
     *
     * @return OrderEntity|OrderOneClickEntity
     */
    private function getEntity($isFastBuy)
    {
        $ajaxShow = $this->getInt('ajax');
        if ($isFastBuy) {
            return new OrderOneClickEntity(
                $this->sectionId,
                true,
                $this->getPost()
            );
        }

        return new OrderEntity($this->sectionId, $ajaxShow, $this->getPost());
    }
}
