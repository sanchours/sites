<?php

namespace skewer\modules\rest\controllers;

use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\build\Adm\Order;
use skewer\build\Adm\Order\ar\GoodsRow as ArGoodsRow;
use skewer\build\Adm\Order\ar\OrderRow;
use skewer\build\Adm\Order\model\ChangeStatus;
use skewer\build\Adm\Order\model\Payments;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Adm\Order\Service as OrderService;
use skewer\build\Page\Cart;
use skewer\build\Page\Cart\Api;
use skewer\build\Tool\DeliveryPayment\Api as DeliveryApi;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use skewer\components\auth\CurrentUser;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\GoodsSelector;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\i18n\ModulesParams;
use Yii\helpers\ArrayHelper;

/**
 * Работа с заказами через rest
 * Class OrdersController.
 */
class OrdersController extends PrototypeController
{
    /** Список полей для списка заказов */
    const LIST_ORDER_FIELDS = 'id, date, status, type_payment, type_delivery, token';
    /** Список полей детальной информации заказа */
    const DETAIL_ORDER_FIELDS = 'id, date, address, person, phone, mail, postcode, status, type_payment, type_delivery, token';
    /** Список полей товаров в заказе */
    const DETAIL_GOODS_FIELDS = 'id_goods, title, price, count, total';
    /** Список полей статусов в истории заказа (без пробелов) */
    const DETAIL_GOODS_HISTORY_FIELDS = 'change_date,status_old,status_new';

    /** Детальная информация заказа */
    public function actionView($id, $sToken = '')
    {
        if (!$sToken and !UsersController::isLogged()) {
            return $this->showError(UsersController::ERR_NOAUTH);
        }

        $oOrderQuery = Order\ar\Order::find()
            ->fields(self::DETAIL_ORDER_FIELDS)
            ->asArray();

        if ($sToken) {
            $oOrderQuery->where('token', $sToken);
        } else {
            $oOrderQuery
                ->where('auth', CurrentUser::getId())
                ->where('id', $id);
        }

        $aOrder = $oOrderQuery->getOne();
        if (!$aOrder) {
            return '';
        }
        $iOrderId = $aOrder['id'];

        $aStatusList = ArrayHelper::map(Status::find()->all(), 'id', 'title');
        $aPaymentList = ArrayHelper::map(Payments::find()->asArray()->all(), 'id', 'title');
        $aDeliveryList = ArrayHelper::map(TypeDelivery::find()->asArray()->all(), 'id', 'title');
        /** @var array $aHistoryList */
        $aHistoryList = ChangeStatus::find()->where(['id_order' => $iOrderId])->asArray()->all();

        // Добавить историю заказа
        if ($aHistoryList) {
            $aAllowedFields = explode(',', self::DETAIL_GOODS_HISTORY_FIELDS);

            foreach ($aHistoryList as $key => &$paItem) {
                $paItem['status_old'] = $aStatusList[$paItem['id_old_status']];
                $paItem['status_new'] = $aStatusList[$paItem['id_new_status']];

                $paItem = array_intersect_key($paItem, array_flip($aAllowedFields));
            }
        }

        $sCurrency = \Yii::t('Order', 'current_currency');

        // Добавление изображение товара
        $aGoodsOrder = Order\ar\Goods::find()->fields(self::DETAIL_GOODS_FIELDS)->where('id_order', $iOrderId)->asArray()->getAll() ?: [];
        foreach ($aGoodsOrder as &$paGoodOrder) {
            $aGoodData = GoodsSelector::get($paGoodOrder['id_goods'], Card::DEF_BASE_CARD, true) ?: [];
            $paGoodOrder += [
                'gallery' => ArrayHelper::getValue($aGoodData, 'fields.gallery.gallery.images.0.images_data', ''),
            ];
        }

        // Обработать/добавить дополнительные поля в выдачу
        $aOrder = array_merge($aOrder, [
            'status' => $aStatusList[$aOrder['status']],
            'type_payment' => ArrayHelper::getValue($aPaymentList, $aOrder['type_payment'], ''),
            'type_delivery' => ArrayHelper::getValue($aDeliveryList, $aOrder['type_delivery'], ''),
            'currency' => "{$sCurrency}",
            'total_price' => Order\Api::getOrderSum($iOrderId),
            'goods' => $aGoodsOrder,
            'history' => $aHistoryList,
        ]);

        return $aOrder + ['order' => &$aOrder];
    }

    /** Получение заказов по id, токену или состоянию авторизации текущего пользователя */
    public function actionIndex()
    {
        // (!) При успешной обработке запроса, но при отсутвии позиций, списковые методы должны отдавать пустой массив, а не строку
        // (!) Заголовки постраничника нужно отдавать всегда при успешной обработке запроса

        $iId = \Yii::$app->request->get('id', '');
        $sToken = \Yii::$app->request->get('token', '');

        if ($iId or $sToken) {
            return $this->actionView($iId, $sToken);
        }

        if (!UsersController::isLogged()) {
            return $this->showError(UsersController::ERR_NOAUTH);
        }

        $iPage = abs((int) \Yii::$app->request->get('page', 0)) ?: 1;
        $iOnPage = abs((int) \Yii::$app->request->get('onpage', 10));
        ($iOnPage <= 50) or ($iOnPage = 50); // Ограничить до 50 позиций на одной странице

        $iTotalCount = 0;
        /** @var array $aOrders */
        $aOrders = Order\ar\Order::find()
            ->fields(self::LIST_ORDER_FIELDS)
            ->where('auth', CurrentUser::getId())
            ->order('id', 'DESC')
            ->limit($iOnPage, $iOnPage * ($iPage - 1))
            ->setCounterRef($iTotalCount)
            ->asArray()
            ->get();

        // ! Постраничник должен устанавливаться для списков даже при пустой выборке
        $this->setPagination($iTotalCount, ceil($iTotalCount / $iOnPage), $iPage, $iOnPage);

        if (!$aOrders) {
            return [];
        }

        $aStatusList = ArrayHelper::map(Status::find()->all(), 'id', 'title');
        $aPaymentList = ArrayHelper::map(Payments::find()->asArray()->all(), 'id', 'title');
        $aDeliveryList = ArrayHelper::map(TypeDelivery::find()->asArray()->all(), 'id', 'title');
        $sCurrency = \Yii::t('Order', 'current_currency');

        // Обработать/добавить дополнительные поля в выдачу
        foreach ($aOrders as &$aOrder) {
            $aOrder = array_merge($aOrder, [
                'status' => $aStatusList[$aOrder['status']],
                'type_payment' => $aPaymentList[$aOrder['type_payment']],
                'type_delivery' => (isset($aDeliveryList[$aOrder['type_delivery']])) ? $aDeliveryList[$aOrder['type_delivery']] : '',
                'currency' => "{$sCurrency}",
                'total_price' => $iTotalPrice = Order\Api::getOrderSum($aOrder['id']),
            ]);
        }

        return $aOrders;
    }

    /** Совершение заказа */
    public function actionCreate()
    {
        $aData = \Yii::$app->request->post();

        // Если есть заказы
        if (isset($aData['objid']) and $aData['objid'] and is_array($aData['objid'])) {

            $aPositions = [];
            foreach ($aData['objid'] as $iKey => $iItemId) {
                $aPositions[] = [
                    'id_item' => $iItemId,
                    'count' => ArrayHelper::getValue($aData, ['count', $iKey], 1),
                ];
            }

            $aDataSend = [
                'is_mobile' => '1', // Флаг заказа из мобильного приложения
                'tp_pay' => '9', // Тип оплаты: Оплата наличными
                'tp_deliv' => '4', // Тип доставки: Самовывоз
            ];
            // Заполнить данные авторизованного пользователя, совместив поля имени пользователя и email таблиц заказов и клиентов
            if (UsersController::isLogged()) {
                $aAuthData = CurrentUser::getProperties();
                $aAuthData['name'] = $aAuthData['name'] ?? 'MobileUser';
                $aAuthData['email'] = $aAuthData['login'] ?? '';
                $aDataSend += $aAuthData;
            }

            /* @var array Данные пользователя */
            $aUserData = (isset($aData['user']) and $aData['user'] and is_array($aData['user'])) ? $aData['user'] : [];

            if (isset($aUserData['person']) && $aUserData['person']) {
                $aUserData['name'] = $aUserData['person'];
            }
            if (isset($aUserData['mail']) && $aUserData['mail']) {
                $aUserData['email'] = $aUserData['mail'];
            }

            $aDataSend += $aUserData;
            $aDataSend += $aData;

            $iOrderId = $this->saveOrder($aDataSend, $aPositions);
            if ($iOrderId && $oOrder = Order\ar\Order::find($iOrderId)) {
                return $this->actionView(0, $oOrder->token);
            }
        }

        return $this->showError(self::ERR_OTHER);
    }

    /**
     * @param $aDataOrder
     * @param $aPositions
     * @return bool
     * @throws \skewer\base\ft\Exception
     */
    public function saveOrder($aDataOrder, $aPositions)
    {
        $orderRow = new OrderRow();
        $orderRow->setData($aDataOrder);

        $dNow = new \DateTime('NOW');
        $orderRow->date = $dNow->format('Y-m-d H:i:s');
        $orderRow->status = Status::getIdByNew();

        $salt = '#*#BaraNeKontritsya322#*#';
        $sha512 = hash('sha512', random_int(0, 1000) . $salt . $orderRow->date);
        $orderRow->token = $sha512;

        // если авторизован, сохраняем id клиента
        if (CurrentUser::isLoggedIn()) {
            $orderRow->auth = CurrentUser::getId();
        }

        $orderId = $orderRow->save();

        /** Заказ корзины */
        $order = new Cart\Order();
        foreach ($aPositions as $aPosition) {
            $position = $order->getExistingOrNew($aPosition['id_item']);
            if (!$position) {
                continue;
            }

            $position['count'] = $aPosition['count'];
            $position['total'] = $position['count'] * $position['price'];
            $order->setItem($position);
        }

        $typeDeliveries = TypeDelivery::findOne($orderRow->type_delivery);
        if ($typeDeliveries) {
            $order->setTypeDelivery($typeDeliveries->attributes);
        }

        $orderRow->price_delivery = $order->getPriceDelivery();
        $orderRow->cache_cart = $this->getCacheCart($order);
        $orderRow->save();

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

        $values = $orderRow->getData();

        // собираем данные для мыла
        $dataForMailOrder = [];
        $changeFields = [
            'tp_deliv' => 'type_delivery',
            'tp_pay' => 'type_payment',
            'name' => 'person',
            'email' => 'mail',
        ];

        $orderEntity = new Cart\OrderEntity(0, false, $aDataOrder);

        /** @var FieldAggregate $field*/
        foreach ($orderEntity->fields as $fieldName => $field) {
            switch ($fieldName) {
                case 'tp_deliv':
                    if ($typeDeliveries) {
                        $fieldName = $changeFields[$fieldName];
                        $values[$fieldName] = $typeDeliveries->title;
                    }
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

        $aOptionsUser = $this->letterForSend($order, $dataForMailOrder, $orderRow);

        if ($orderRow->mail) {
            $sTitle = ModulesParams::getByName('order', 'title_user_mail');
            $sBody = ModulesParams::getByName('order', 'user_content');

            OrderService::sendMail($orderRow->mail, $sTitle, $sBody, $aOptionsUser);
        }

        $sTitle = ModulesParams::getByName('order', 'title_adm_mail');
        $sBody = ModulesParams::getByName('order', 'adm_content');
        $aOptionsAdm = $this->letterForSend($order, $dataForMailOrder, $orderRow, true);

        OrderService::sendMail(Site::getAdminEmail(), $sTitle, $sBody, $aOptionsAdm);

        return $orderId;
    }

    private function letterForSend(
        Cart\Order $oOrderCart,
        array $dataForMail,
        OrderRow $orderRow,
        $admin = false
    )
    {
        $aDataSend = [
            'date' => $orderRow->date,
            'orderId' => $orderRow->id,
        ];

        if (($admin && !$this->isSendDataInLetterAdmin()) || !$admin) {
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

        return [
            'order_id' => $orderRow->id,
            'token' => $orderRow->token,
            'order_info' => $orderTemplate,
        ];
    }

    /**
     * @return bool
     */
    private function isSendDataInLetterAdmin()
    {
        $orderEntity = new Cart\OrderEntity(0);
        return (bool)$orderEntity->formAggregate->settings->noSendDataInLetter;
    }

    /**
     * @param Cart\Order $order
     * @return false|string
     */
    private function getCacheCart(Cart\Order $order)
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
}
