<?php

namespace skewer\build\Page\Profile;

use skewer\base\log\Logger;
use skewer\base\section\Tree;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Adm\Order;
use skewer\build\Adm\Order\ar;
use skewer\build\Adm\Order\model\ChangeStatus;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Page\Auth\Api;
use skewer\build\Page\Cart;
use skewer\build\Page\WishList;
use skewer\build\Page\WishList\WishListEvent;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;
use skewer\build\Tool\Payments as Payments;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\CurrentUserPrototype;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\GoodsSelector;
use skewer\components\forms\FormBuilder;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class Module extends site_module\page\ModulePrototype
{
    public $prm = 0;

    private $aStatusList = [];
    private $newStatus;

    /**
     * Метод - исполнитель функционала.
     */
    public $sInfoTemplate = 'info.twig';

    /** @var string шаблон неавторизованного пользователя */
    public $sNoAuthTemplate = 'no_auth.twig';

    /** @var string шаблон смены пароля */
    public $sSettingsTemplate = 'settings.twig';

    /** @var string шаблон лк */
    public $sAuthTemplate = 'view.twig';

    /** @var string шаблон детального состояния заказа в лк */
    public $sOrderDetailTemplate = 'detail.twig';

    /** @var string шаблон отложенных товаров */
    public $sWishListTemplate = 'wishlist.twig';

    public function execute()
    {
        $idProfile = Tree::getSectionByPath(Api::getProfilePath());
        $this->setData('profile_id', $idProfile);
        $this->setData('profile_url', Api::getProfilePath());
        $this->setData('auth_url', Api::getAuthPath());

        /*
         * отключаем счетчики в личном кабинете
         * если надо будет где еще, вынести в отдельный метод
         */
        $this->oContext->getParentProcess()->setData('counters', '');
        $this->oContext->getParentProcess()->setData('countersCode', '');

        $this->aStatusList = Status::getListTitle();

        $sCmd = $this->getStr('cmd', 'init');

        $this->setData('page', $this->sectionId());
        $this->setData('wishlist', WishList\WishList::isModuleOn());

        $user = CurrentUser::getUserData();
        $canChangePass = isset($user['network']) && $user['network'] ? false : true;
        $this->setData('canChangePass', $canChangePass);

        $sActionName = 'action' . ucfirst($sCmd);

        if (method_exists($this, $sActionName)) {
            $this->{$sActionName}();
        } else {
            throw new NotFoundHttpException();
        }

        return psComplete;
    }

    /**
     * Список заказов у пользователя.
     *
     * @param int $iUserId id User
     * @param int $iOrderId id заказа, если нет взять все товары пользователя
     * @param string $sToken токен
     * @param int $iLimit Число позиций
     * @param int $iSkip Пропуск позиций
     * @param int $iTotalCount Ссылка на переменную общего числа заказов
     *
     * @return array Массив заказаов клиента
     */
    private function getOrders($iUserId, $iOrderId = 0, $sToken = '', $iLimit = 1, $iSkip = 0, &$iTotalCount = null)
    {
        if ($sToken) {
            $oQueryOrders = ar\Order::find()->where('token', $sToken);
        } elseif ($iOrderId) {
            $oQueryOrders = ar\Order::find()->where('auth', $iUserId)->where('id', $iOrderId)->order('id', 'DESC');
        } else {
            $oQueryOrders = ar\Order::find()->where('auth', $iUserId)->order('id', 'DESC');
        }

        $aOrders = $oQueryOrders->index('id')->limit($iLimit, $iSkip)->setCounterRef($iTotalCount)->asArray()->getAll();

        /** Информация о товарах заказов клиента */
        $aGoodsStatistic = (!$aOrders) ? [] : Order\Api::getGoodsStatistic(array_keys($aOrders));

        /** Закэшированные формы систем оплат текущего пользователя */
        $aPayments = [];

        foreach ($aOrders as $iOrderId => &$aOrderItem) {
            // Записать текстовый статус заказа
            $aOrderItem['text_status'] = $this->getStatusText($aOrderItem['status']);

            // Установить общую информацию о товарах заказа
            isset($aGoodsStatistic[$iOrderId]) and $aOrderItem['goods'] = $aGoodsStatistic[$iOrderId];

            if (isset($aGoodsStatistic[$iOrderId])) {
                $aOrderItem['sum_to_pay'] = $aGoodsStatistic[$iOrderId]['sum'] + $aOrderItem['price_delivery'];
                $aOrderItem['delivery_parameters'] = Order\Api::getArrayCacheCart($aOrderItem['cache_cart'], $aOrderItem['price_delivery']);
            }

            // Установить кнопки оплаты для каждого заказа
            if (isset($aGoodsStatistic[$iOrderId]) and ($aOrderItem['status'] == $this->getNewStatusId())) {
                /** @var $oPaymentType TypePayment */
                $oPaymentType = TypePayment::findOne(['id' => $aOrderItem['type_payment']]);
                if ($oPaymentType && $oPaymentType->payment) {
                    /* @var Payments\Payment $oPayment */
                    if (!isset($aPayments[$oPaymentType->payment]) || !$aPayments[$oPaymentType->payment]) {
                        $aPayments[$oPaymentType->payment] = Payments\Api::make($oPaymentType->payment);
                    }

                    $oPayment = $aPayments[$oPaymentType->payment];

                    if ($oPayment) {
                        $oPayment->setOrderId($aOrderItem['id']);
                        $oPayment->setSum($aGoodsStatistic[$iOrderId]['sum'] + $aOrderItem['price_delivery']);
                        $aOrderItem['paymentForm'] = $oPayment->getForm();
                    }
                }
            }
        }

        return $aOrders;
    }

    //** Получить товары заказа */
    private function getGoods($iOrderId)
    {
        try {
            $aGoods = ar\Goods::find()->where('id_order', $iOrderId)->asArray()->getAll();

            // Добавить поля из каталожной информации о товаре
            foreach ($aGoods as &$aGood) {
                $sGoods = GoodsRow::get($aGood['id_goods']);
                if ($sGoods) {
                    $aGood['show_detail'] = !Card::isDetailHiddenByCard($sGoods->getExtRow()->getModel()->getName());
                } else {
                    $aGood['show_detail'] = false;
                }
                if ($aGood['id_goods'] and $aGoodData = GoodsSelector::get($aGood['id_goods'], 1)) {
                    $aGood += [
                        'url' => $aGoodData['url'],
                        'image' => ArrayHelper::getValue($aGoodData, 'fields.gallery.first_img.images_data.mini.file', ''),
                    ];
                }
            }
        } catch (\Exception $e) {
            Logger::error(\Yii::t('auth', 'error_find_good') . $e->getMessage());

            return false;
        }

        return $aGoods;
    }

    /**
     * Форма инфы по юзеру.
     *
     * @throws UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionInfo()
    {
        if (!CurrentUser::isLoggedIn()) {
            throw new UnauthorizedHttpException();
        }

        $editProfileEntity = new EditProfileEntity($this->getPost());

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $editProfileEntity,
            $this->sectionId(),
            $label
        );

        if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
            $this->setData('msg', \Yii::t('auth', 'msg_save'));
        }

        Auth::loadUser(CurrentUserPrototype::$sLayer, CurrentUser::getId());

        $this->setData('form', $formBuilder->getFormTemplate());
        $this->setData('cmd', 'info');
        $this->setTemplate($this->sInfoTemplate);
    }

    /**
     * Форма смены пароля.
     *
     * @throws UnauthorizedHttpException
     * @throws \Exception
     *
     * @return int
     */
    public function actionSettings()
    {
        $user = CurrentUser::getUserData();

        if (!empty($user['network'])) {
            $this->setData('isSocialNetworkUser', true);
            $this->setTemplate($this->sNoAuthTemplate);

            return psComplete;
        }
        if (!CurrentUser::isLoggedIn()) {
            throw new UnauthorizedHttpException();
        }

        $newPassEntity = new NewPassEntity($this->getPost());

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $newPassEntity,
            $this->sectionId(),
            $label
        );

        if ($formBuilder->hasSendData()) {
            if ($formBuilder->validate() && $formBuilder->save()) {
                $this->setData('msg', \Yii::t('auth', 'msg_pass_save'));
                //переинициализация
                $formBuilder = new FormBuilder(
                    new NewPassEntity(),
                    $this->sectionId(),
                    $label
                );
            } else {
                $this->setData('msg', \Yii::t('auth', 'msg_pass_no_save'));
            }
        }
        $this->setData('forms', $formBuilder->getFormTemplate());
        $this->setData('cmd', $newPassEntity->cmd);
        $this->setTemplate($this->sSettingsTemplate);
        $this->setData('profile2_url', 'asd');

        return psComplete;
    }

    /**
     * Форма по дефолту заказа.
     */
    public function actionInit()
    {
        // проверить авторизацию
        if (!CurrentUser::isLoggedIn()) {
            throw new UnauthorizedHttpException();
        }

        $this->setData('current_user', CurrentUser::getUserData());
        $this->setData('cmd', 'order');

        $iPage = $this->getInt('page', 1);
        $iOnPage = Cart\Api::maxOrdersOnPage();
        $iTotalCount = 0;

        $this->setData('orders', $this->getOrders(CurrentUser::getId(), 0, '', $iOnPage, $iOnPage * ($iPage - 1), $iTotalCount));

        $this->getPageLine($iPage, $iTotalCount, $this->sectionId(), ['cmd' => 'init'], ['onPage' => $iOnPage], 'aPages', true);

        \Yii::$app->session->setFlash('profile_page', $_SERVER['REQUEST_URI']);

        $this->setTemplate($this->sAuthTemplate);
    }

    public function actionDetail()
    {
        $iOrderId = $this->getInt('id');
        $sToken = $this->getStr('token', '');

        if ($sToken) {
            $aOrders = $this->getOrders(CurrentUser::getId(), 0, $sToken);
        } elseif ($iOrderId) {
            if (!CurrentUser::isLoggedIn()) {
                throw new UnauthorizedHttpException();
            }
            $aOrders = $this->getOrders(CurrentUser::getId(), $iOrderId);
        } else {
            throw new NotFoundHttpException();
        }

        if (!$aOrders) {
            throw new NotFoundHttpException();
        }
        $aOrder = reset($aOrders);
        $aOrder['type_payment_text'] = \skewer\build\Tool\DeliveryPayment\Api::getTitleTypePayment($aOrder['type_payment']);
        $aOrder['type_delivery_text'] = \skewer\build\Tool\DeliveryPayment\Api::getTitleTypeDelivery($aOrder['type_delivery']);

        $aOrderGoods = $this->getGoods($aOrder['id']);

        if ($sToken) {
            $this->setData('token', $sToken);
        }

        $aHistoryList = ChangeStatus::find()->where(['id_order' => $aOrder['id']])->asArray()->all();
        if ($aHistoryList) {
            foreach ($aHistoryList as $k => $item) {
                $aHistoryList[$k]['title_old_status'] = $this->getStatusText($item['id_old_status']);
                $aHistoryList[$k]['title_new_status'] = $this->getStatusText($item['id_new_status']);
            }
            $this->setData('historyList', $aHistoryList);
        }

        $sPrevPage = \Yii::$app->session->get('profile_page');
        if ($sPrevPage !== null) {
            $this->setData('backlink', $sPrevPage);
        }

        $this->setData('order', $aOrder);

        $orderFields = (new TemplateDetail())->getDetailOrderFields($aOrder);
        $this->setData('orderFields', $orderFields);

        if ($aOrderGoods) {
            $this->setData('items', $aOrderGoods);
        }

        $this->setTemplate($this->sOrderDetailTemplate);
    }

    /**
     * Форма отложенных товаров.
     */
    public function actionWishList()
    {
        if (!SysVar::get('WishList.Enable')) {
            throw new NotFoundHttpException();
        }

        if (!CurrentUser::isLoggedIn()) {
            throw new UnauthorizedHttpException();
        }

        //Найдем сообщения для пользователя
        $aMessages = WishListEvent::popMessageForUser(CurrentUser::getId());

        $this->setData('aMessages', $aMessages);

        $this->setData('current_user', CurrentUser::getUserData());
        $this->setData('cmd', 'wishlist');
        $WishList = new WishList\WishList();

        $this->setTemplate($this->sWishListTemplate);

        $iPage = $this->getInt('page', 1);
        $iSectionId = $this->sectionId();
        $WishList->setLimit($iPage);
        $aURLParams = ['cmd' => 'wishlist'];
        $this->getPageLine(
            $iPage,
            $WishList->getCount(),
            $iSectionId,
            $aURLParams,
            ['onPage' => SysVar::get('WishList.OnPage')],
            'aPages',
            true
        );

        $aWishList = $WishList->getList();
        $this->setData('Wishlist', $WishList);
        $this->setData('aWishList', $aWishList);

        if (WishList\WishList::isModuleOn()) {
            $sHtml = $this->createAndExecuteProcess('WishList', WishList\Module::className(), []);
            $this->setData('aConfirmTexts', $sHtml);
        }

        if ($WishList->IsAuthorisedUser()) {
            $this->setData('authorized', 'true');
        }
    }

    /**
     * Отдает название статуса по id.
     *
     * @param $mId
     *
     * @return string
     */
    private function getStatusText($mId)
    {
        if (isset($this->aStatusList[$mId])) {
            return $this->aStatusList[$mId];
        }

        return '---';
    }

    /**
     * Ид статуса нового заказа.
     *
     * @return int
     */
    private function getNewStatusId()
    {
        if ($this->newStatus === null) {
            $this->newStatus = Status::getIdByNew();
        }

        return $this->newStatus;
    }
}
