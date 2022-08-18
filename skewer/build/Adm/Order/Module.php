<?php

namespace skewer\build\Adm\Order;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\base\SysVar;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Catalog\Goods\model\GoodsForOrderEditorList;
use skewer\build\Page\Cart\Api as ApiCart;
use skewer\build\Page\Cart\OrderEntity;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use skewer\components\auth\CurrentAdmin;
use skewer\components\catalog;
use skewer\components\forms\service\FormService;
use skewer\components\i18n\Languages;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Transliterate;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    /** @var int Текущий номер станицы постраничника */
    public $iPageNum = 0;

    protected $iStatusFilter = 0;

    protected $sLanguageFilter = '';

    // фильтр по дате
    protected $mDateFilter1 = '';
    protected $mDateFilter2 = '';

    // фильтр по клиентам
    protected $sPersonFilter = '';

    // фильтр по номеру заказа
    protected $sIdFilter = '';

    /** @var FormService $_serviceForm */
    private $_serviceForm;

    protected function preExecute()
    {
        // Получить номер страницы
        $this->iPageNum = $this->getInt('page', $this->getInnerData('page', 0));
        $this->setInnerData('page', $this->iPageNum);

        $this->iStatusFilter = $this->getInt('filter_status', 0);

        $sLanguage = \Yii::$app->language;
        if ($this->sectionId()) {
            $sLanguage = Parameters::getLanguage($this->sectionId()) ?: $sLanguage;
        }

        $this->sLanguageFilter = $this->get('filter_language', $sLanguage);

        $this->sPersonFilter = $this->getStr('filter_person', $this->sPersonFilter);
        $this->sIdFilter = $this->getStr('filter_id', $this->sIdFilter);
        $this->mDateFilter1 = $this->get('date1', $this->mDateFilter1);
        $this->mDateFilter2 = $this->get('date2', $this->mDateFilter2);

        $this->_serviceForm = new FormService();
    }

    /**
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        parent::setServiceData($oIface);

        // расширение массива сервисных данных
        $oIface->setServiceData([
            'filter_status' => $this->iStatusFilter,
            'filter_language' => $this->sLanguageFilter,
        ]);
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->get('data');

        // id записи
        $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;

        if (!$iItemId) {
            $iItemId = $this->getInnerData('orderId');
        }

        $oOrder = ar\Order::find($iItemId);
        $oOrder->delete();

        // вывод списка
        $this->actionInit();
    }

    protected function actionDeleteAllOrders()
    {
        ar\Order::delete()->get();
        ar\Goods::delete()->get();
        // вывод списка
        $this->actionInit();
    }

    protected function actionDeleteMultiple()
    {
        $aData = $this->get('data');
        if ($aData['items']) {
            $aId = [];
            foreach ($aData['items'] as $aItem) {
                $aId[] = $aItem['id'];
            }

            ar\Order::delete()->where('id', $aId)->get();
            ar\Goods::delete()->where('id_order', $aId)->get();
        }

        $this->actionInit();
    }

    /**
     * Сохранение заказа.
     *
     * @throws UserException
     * @throws \yii\base\Exception
     * @throws ui\ORMSaveException
     */
    protected function actionSave()
    {
        // запросить данные
        $aData = $this->get('data');
        $iId = $this->getInnerDataInt('orderId');

        if (!$aData) {
            throw new UserException('Empty data');
        }

        if ($iId) {
            /**
             * @var ar\OrderRow
             */
            $oRow = ar\Order::find($iId);

            if (!$oRow) {
                throw new UserException("Not found [{$iId}]");
            }
            $aCacheCart = json_decode($oRow->cache_cart, true);
            $aCacheCart = ['hide_price_fractional' => $aCacheCart['hide_price_fractional']];

            if ($paidDelivery = SysVar::get(\skewer\build\Tool\DeliveryPayment\Api::PAID_DELIVERY)) {
                if ($aData['type_delivery'] && $aData['type_delivery'] != $oRow->type_delivery) {
                    $oTypeDelivery = TypeDelivery::findOne(['id' => $aData['type_delivery']]);
                    if (!$oTypeDelivery) {
                        throw new UserException("Not found type delivery [{$oRow->type_delivery}]");
                    }
                    $aParametersDelivery = ['paid_delivery' => $paidDelivery];
                    $totalPrice = Api::getOrderSum($iId);
                    $aData['price_delivery'] = $oTypeDelivery->price;
                    if ($oTypeDelivery->free_shipping && $totalPrice >= $oTypeDelivery->min_cost) {
                        $aData['price_delivery'] = 0;
                        $aParametersDelivery['free_shipping'] = \Yii::t('order', 'freeShipping');
                    }
                    if ($oTypeDelivery->coord_deliv_costs) {
                        $aData['price_delivery'] = 0;
                        $aParametersDelivery['coord_deliv_costs'] = \Yii::t('order', 'coordDelivCosts');
                    }
                    $aData['cache_cart'] = json_encode(array_merge($aParametersDelivery, $aCacheCart));
                }
            } else {
                $aData['price_delivery'] = 0;
                $aData['cache_cart'] = json_encode($aCacheCart);
            }

            $iOldStatus = $oRow->status;

            $oRow->setData($aData);

            if (!$oRow->save()) {
                throw new ui\ORMSaveException($oRow);
            }

            if (isset($aData['status']) && $iOldStatus != $aData['status']) {
                Service::sendMailChangeOrderStatus($iId, $iOldStatus, $aData['status']);
            }
        } else {
            $oRow = ar\Order::getNewRow($aData);
            if (!$oRow->save()) {
                throw new ui\ORMSaveException($oRow);
            }
        }

        // вывод списка
        $this->actionInit();
    }

    /**
     * @throws UserException
     */
    protected function actionShow()
    {
        // номер заказа
        $aData = $this->get('data');

        $iItemId = (is_array($aData) && isset($aData['id'])) ? (int) $aData['id'] : 0;
        $iItemId = $this->getInDataVal('id_order', $iItemId);

        $statusList = Status::getListTitle();
        $aHistoryList = Adm\Order\model\ChangeStatus::find()->asArray()->where(['id_order' => $iItemId])->all();

        $aOrder = ar\Order::find()->where('id', $iItemId)->asArray()->getOne();

        if (!$aOrder) {
            throw new UserException('Item not found');
        }
        $paymentList = Api::getPaymentList($aOrder['type_delivery']);
        $deliveryList = Api::getDeliveryList();

        if ($aHistoryList) {
            foreach ($aHistoryList as $k => $item) {
                $aHistoryList[$k]['title_old_status'] = ArrayHelper::getValue($statusList, $item['id_old_status'], sprintf('--- [' . $item['id_old_status'] . ']'));
                $aHistoryList[$k]['title_new_status'] = ArrayHelper::getValue($statusList, $item['id_new_status'], sprintf('--- [' . $item['id_new_status'] . ']'));
            }
            $aOrder['history'] = $this->renderTemplate('historyList.twig', ['historyList' => $aHistoryList]);
        }

        $sText = '';
        $totalPrice = Api::getOrderSum($aOrder['id']);
        $aDeliveryParameters = Api::getArrayCacheCart($aOrder['cache_cart'], $aOrder['price_delivery']);
        $aGoods = ar\Goods::find()->where('id_order', $aOrder['id'])->asArray()->getAll();

        if (is_array($aGoods) && count($aGoods) !== 0) {
            $aGoodsObjects = catalog\GoodsSelector::getListByIds(ArrayHelper::getColumn($aGoods, 'id_goods'), catalog\Card::DEF_BASE_CARD, false)->parse();
            if (count($aGoodsObjects) !== 0) {
                $aGoodsObjects = ArrayHelper::index($aGoodsObjects, 'id');
                foreach ($aGoods as $k => $item) {
                    $aGoods[$k]['object'] = isset($aGoodsObjects[$item['id_goods']]) ? $aGoodsObjects[$item['id_goods']] : null;
                }
            }

            $sText = $this->renderTemplate(
                'admin.goods.twig',
                ['id' => $aOrder['id'],
                    'aGoods' => $aGoods,
                    'totalPrice' => $totalPrice,
                    'deliveryParameters' => $aDeliveryParameters,
                    'deliveryPrice' => $aDeliveryParameters['price_delivery'],
                    'totalPriceToPay' => ($totalPrice + $aOrder['price_delivery']),
                    'isArticle' => \skewer\build\Page\Cart\Api::isArticle(), ]
            );
        }

        $aOrder['good'] = $sText;

        if (isset($aOrder['id']) && $aOrder['id']) {
            $this->setInnerData('orderId', $aOrder['id']);
        }
        $this->render(new view\Show([
            'aStatusList' => $statusList,
            'aPaymentList' => $paymentList,
            'aDeliveryList' => $deliveryList,
            'aOrder' => $aOrder,
            'aFormRowKey' => $this->getExistFieldForm(),
        ]));
    }

    public function actionLoadTypePayment()
    {
        $aFormData = $this->get('formData', []);
        $iTypeDelivery = $aFormData['type_delivery'] ?? 0;
        $iTypePayment = $aFormData['type_payment'] ?? 0;
        $paymentList = Api::getPaymentList($iTypeDelivery);

        $loadTypePayment = new Adm\Order\view\LoadTypePayment([
            'aItem' => ['type_payment' => $iTypePayment],
            'aPaymentList' => $paymentList,
            'aFormRowKey' => $this->getExistFieldForm(),
        ]);

        $loadTypePayment->build();
        $this->setInterfaceUpd($loadTypePayment->getInterface());
    }

    /**
     * Получение массива для проверки существования полей.
     *
     * @return  array
     */
    private function getExistFieldForm()
    {
        $formAggregate = $this->_serviceForm->getFormByName(
            OrderEntity::tableName()
        );
        $keysFields = array_keys($formAggregate->fields);

        if (in_array('name', $keysFields)) {
            $keysFields[] = 'person';
        }

        if (in_array('email', $keysFields)) {
            $keysFields[] = 'mail';
        }

        return $keysFields;
    }

    /**
     * Список заказов.
     */
    protected function actionList()
    {
        $aStatusList = Status::getListTitle();

        /** Общее число заказов */
        $iCount = 0;

        $oQuery = ar\Order::find()
            ->index('id')
            ->limit(self::maxOrdersOnPage(), $this->iPageNum * self::maxOrdersOnPage())
            ->setCounterRef($iCount)
            ->order('id', 'DESC');

        if ($this->mDateFilter1 && $this->mDateFilter2) {
            $date = [
                $this->mDateFilter1,
                $this->mDateFilter2 . ' 23:59:59', ];
            $oQuery->andWhere('date BETWEEN ?', $date);
        } elseif ($this->mDateFilter1) {
            $oQuery->andWhere('date >= ?', $this->mDateFilter1);
        } elseif ($this->mDateFilter2) {
            $oQuery->andWhere('date <= ?', $this->mDateFilter2 . ' 23:59:59');
        }

        if ($this->sPersonFilter) {
            $oQuery->where('person LIKE ?', "%{$this->sPersonFilter}%");
        }
        if ($this->sIdFilter) {
            $oQuery->where('id', $this->sIdFilter);
        }
        if ($this->iStatusFilter) {
            $oQuery->where('status', $this->iStatusFilter);
        }

        $aOrders = $oQuery->asArray()->getAll();

        // Добавить общую сумму заказа
        $aGoodsSatistic = Api::getGoodsStatistic(array_keys($aOrders));
        foreach ($aOrders as $iOrderId => &$aOrder) {
            $aOrder['total_price'] = isset($aGoodsSatistic[$iOrderId]) ? round($aGoodsSatistic[$iOrderId]['sum'] + $aOrder['price_delivery'], 3) : '-';
        }

        $this->render(new view\Index([
            'aItems' => $aOrders,
            'mDateFilter1' => $this->mDateFilter1,
            'mDateFilter2' => $this->mDateFilter2,
            'sPersonFilter' => $this->sPersonFilter,
            'sIdFilter' => $this->sIdFilter,
            'aStatusList' => $aStatusList,
            'iStatusFilter' => $this->iStatusFilter,
            'onPage' => self::maxOrdersOnPage(),
            'page' => $this->iPageNum,
            'total' => $iCount,
        ]));
    }

    /**
     * Получение значений полей фильтра.
     *
     * @param string[] $aFilterFields Список полей фильтра
     *
     * @return array
     */
    protected function getFilterVal($aFilterFields)
    {
        $aFilter = [];

        foreach ($aFilterFields as $sField) {
            $sName = 'filter_' . $sField;
            $sVal = $this->getStr($sName, $this->getInnerData($sName, ''));
            $this->setInnerData($sName, $sVal);
            $aFilter[$sField] = $sVal;
        }

        return $aFilter;
    }

    /**
     * форма добавление нового товара.
     */
    protected function actionShowAddGoodsList()
    {
        $model = GoodsForOrderEditorList::get()
            ->getWithoutOrderedGoods($this->getInnerDataInt('orderId'))
            ->setFilter($this->getFilterVal(['title', 'section']))
            ->limit($this->iPageNum, self::maxOnPageGoods());

        $this->render(new view\AddGoods([
            'model' => $model,
        ]));
    }

    /**
     * Добавление нового товара в заказ.
     *
     * @throws UserException
     */
    public function actionAddGoods()
    {
        $this->setInnerData('filter_section', $this->sectionId());
        $data = $this->getInData();
        $list = [];
        if ($this->getInDataVal('multiple')) {
            $items = ArrayHelper::getValue($data, 'items', []);
            if ($items) {
                $list = array_column($items, 'id');
            }
        } else {
            if ($id = $this->getInDataVal('id')) {
                $list[] = $id;
            }
        }

        $iOrderId = $this->getInnerDataInt('orderId');
        $oNewGood = new ar\Goods();

        if (count($list)) {
            $aNewGoods = Query::SelectFrom('co_base_card')
                ->fields(['id', 'title', 'price'])
                ->where('id', $list)
                ->asArray()
                ->getAll();

            foreach ($aNewGoods as $aGood) {
                $aNewGoodOrder = [
                    'id_goods' => $aGood['id'],
                    'title' => $aGood['title'],
                    'total' => $aGood['price'],
                    'price' => $aGood['price'],
                    'count' => 1,
                    'id_order' => $iOrderId,
                ];
                $oNewGood::getNewRow($aNewGoodOrder)
                    ->save();
            }

            //обновляем данные о доставке оплате
            $this->updateDataDeliveryPayment($iOrderId);
        }
        $this->actionGoodsShow($iOrderId);
    }

    protected function actionEmailShow()
    {
        if (!$this->sectionId()) {
            $aLanguages = Languages::getAllActive();
            $aLanguages = ArrayHelper::map($aLanguages, 'name', 'title');
            $aData['aLanguages'] = $aLanguages;
        }

        $aModulesData = ModulesParams::getByModule('order', $this->sLanguageFilter);
        $this->setInnerData('languageFilter', $this->sLanguageFilter);

        $aItems = [];
        $aItems['info'] = \Yii::t('order', 'head_mail_text', [\Yii::t('app', 'site_label'), \Yii::t('app', 'url_label')]);
        $aItems['title_change_status_mail'] = ArrayHelper::getValue($aModulesData, 'title_change_status_mail', '');
        $aItems['status_content'] = ArrayHelper::getValue($aModulesData, 'status_content', '');
        $aItems['title_user_mail'] = ArrayHelper::getValue($aModulesData, 'title_user_mail', '');
        $aItems['user_content'] = ArrayHelper::getValue($aModulesData, 'user_content', '');
        $aItems['title_adm_mail'] = ArrayHelper::getValue($aModulesData, 'title_adm_mail', '');
        $aItems['adm_content'] = ArrayHelper::getValue($aModulesData, 'adm_content', '');
        $aItems['status_paid_content'] = ArrayHelper::getValue($aModulesData, 'status_paid_content', '');
        $aItems['title_status_paid'] = ArrayHelper::getValue($aModulesData, 'title_status_paid', '');
        $aItems['title_change_order'] = ArrayHelper::getValue($aModulesData, 'title_change_order', '');
        $aItems['content_change_order'] = ArrayHelper::getValue($aModulesData, 'content_change_order', '');

        $aData['sLanguageFilter'] = $this->sLanguageFilter;
        $aData['aItems'] = $aItems;

        $this->render(new view\EmailShow($aData));
    }

    protected function actionEmailSave()
    {
        $aData = $this->getInData();

        $aKeys =
            [
                'title_change_status_mail', 'status_content',
                'status_paid_content', 'title_status_paid',
                'title_user_mail', 'user_content',
                'title_adm_mail', 'adm_content',
                'title_change_order', 'content_change_order',
            ];

        $sLanguage = $this->getInnerData('languageFilter');
        $this->setInnerData('languageFilter', '');

        if ($sLanguage) {
            foreach ($aData as $sName => $sValue) {
                if (!in_array($sName, $aKeys)) {
                    continue;
                }

                ModulesParams::setParams('order', $sName, $sLanguage, $sValue);
            }
        }

        $this->actionEmailShow();
    }

    /**
     * Быстрое сохранение прям из листа.
     *
     * @throws UserException
     */
    protected function actionEditDetailGoods()
    {
        // запросить данные
        $aData = $this->get('data');

        $sPrice = \skewer\build\Page\Cart\Api::priceFormat(str_replace(',', '.', $aData['price']));
        $aData['price'] = $sPrice;
        $sCount = $aData['count'];

        // проверяем, чтоб админ не ввел бурду
        if (is_numeric($sPrice) && is_numeric($sCount)) {
            $iId = $this->getInDataValInt('id');
            if ($iId) {
                /**
                 * @var ar\GoodsRow
                 */
                $oRow = ar\Goods::find($iId);
                if (!$oRow) {
                    throw new UserException("Запись [{$iId}] не найдена");
                }
                if (!$sPrice) {
                    $sPrice = \skewer\build\Page\Cart\Api::priceFormat($oRow->price);
                    $aData['price'] = \skewer\build\Page\Cart\Api::priceFormat($oRow->price);
                }

                $total = $sPrice * $sCount;
                if ($total > 0) {
                    $aData['total'] = $total;
                }

                $oRow->setData($aData);
                $oRow->save();

                $oRow = ar\Goods::find($iId);
                $aData = $oRow->getData();

                $this->updateRow($aData);

                //обновляем данные о доставке оплате
                $this->updateDataDeliveryPayment($aData['id_order']);
            }
        } else {
            throw new UserException(\Yii::t('order', 'valid_data_error'));
        }
    }

    /**
     * @throws UserException
     */
    protected function actionDeleteDetailGoods()
    {
        // запросить данные
        $aData = $this->get('data');

        // id записи
        $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;
        ar\Goods::delete($iItemId);

        //обновляем данные о доставке оплате
        $this->updateDataDeliveryPayment($aData['id_order']);

        $this->actionGoodsShow($aData['id_order']);
    }

    protected function actionGoodsShow($id = 0)
    {
        // номер заказа
        $iItemId = $this->getInnerDataInt('orderId');

        if (!$iItemId || $id) {
            $iItemId = $id;
        }

        $aItems = ar\Goods::find()->where('id_order', $iItemId)->asArray()->getAll();

        foreach ($aItems as &$item) {
            $item['price'] = \skewer\build\Page\Cart\Api::priceFormat(str_replace(',', '.', $item['price']));
            $item['total'] = \skewer\build\Page\Cart\Api::priceFormat(str_replace(',', '.', $item['total']));
        }

        $this->render(new view\GoodsShow([
            'aItems' => $aItems,
            'iItemId' => $iItemId,
        ]));
    }

    protected function actionStatusList()
    {
        $this->render(new view\StatusList([
            'bIsSystemMode' => CurrentAdmin::isSystemMode(),
            'aItems' => Status::getList(),
        ]));
    }

    protected function actionStatusShow()
    {
        $sName = $this->getInDataVal('name');

        if ($sName) {
            $oStatus = Status::find()
                ->where(['name' => $sName])
                ->multilingual()
                ->one();
        } else {
            $oStatus = new Status();
        }

        $aLanguages = Languages::getAllActive();

        if (count($aLanguages) > 1) {
            $sLabel = 'status_title_lang';
        } else {
            $sLabel = 'status_title';
        }

        $this->render(new view\StatusShow([
            'bIsSystemMode' => CurrentAdmin::isSystemMode(),
            'bIsNewRecord' => $oStatus->isNewRecord,
            'aLanguages' => Languages::getAllActive(),
            'sLabel' => $sLabel,
            'aData' => $oStatus->getAllAttributes(),
        ]));
    }

    /**
     * @throws UserException
     */
    protected function actionStatusDelete()
    {
        $name = ArrayHelper::getValue($this->get('data'), 'name', '');
        if ($name) {
            if (!Status::canBeDeleted($name)) {
                throw new UserException(\Yii::t('order', 'status_delete_error'));
            }
            Status::deleteAll(['name' => $name]);
        }

        $this->actionStatusList();
    }

    /**
     * Сохранение статуса.
     *
     * @throws UserException
     */
    protected function actionStatusSave()
    {
        $aData = $this->get('data');
        $sName = $this->getInDataVal('name');
        if (empty($sName)) {
            $sName = Transliterate::change($aData['title_ru']);
        }

        if (!$aData || !$sName) {
            throw new UserException(\Yii::t('order', 'valid_data_error'));
        }
        if (isset($aData['title'])) {
            $aData['title_' . \Yii::$app->language] = $aData['title'];
        }

        /**
         * @var Status
         */
        $oStatus = Status::find()
            ->multilingual()
            ->where(['name' => $sName])
            ->one();

        if (!$oStatus) {
            $oStatus = new Status($aData);
        } else {
            $oStatus->setAttributes($aData);
        }

        $oStatus->setLangData($aData);

        $oStatus->save();

        $this->actionStatusList();
    }

    /** Состояние: Настройки для всех заказов */
    protected function actionEditSettings()
    {
        $this->render(new view\EditSettings([
            'aItems' => [
                'order_max_size' => ApiCart::maxOrderSize(),
                'onpage_profile' => ApiCart::maxOrdersOnPage(),
                'onpage_cms' => self::maxOrdersOnPage(),
                'onpage_goods' => self::maxOnPageGoods(),
            ],
        ]));
    }

    /** Действие: сохранить настройки для всех заказов */
    protected function actionSaveSettings()
    {
        $aData = $this->get('data');

        isset($aData['onpage_goods']) and self::maxOnPageGoods($aData['onpage_goods']);
        isset($aData['order_max_size']) and ApiCart::maxOrderSize($aData['order_max_size']);
        isset($aData['onpage_profile']) and ApiCart::maxOrdersOnPage($aData['onpage_profile']);
        isset($aData['onpage_cms']) and self::maxOrdersOnPage($aData['onpage_cms']);

        $this->actionList();
    }

    /** Получить/установить максильное число отображаемых товаров при добавлении товара в заказ */
    private static function maxOnPageGoods()
    {
        return func_num_args() ? SysVar::set('Order.onpage_goods', (int) func_get_arg(0)) : SysVar::get('Order.onpage_goods', 100);
    }

    protected function actionMailUpdGoodsList()
    {
        $iId = $this->getInnerDataInt('orderId');

        $aOrder = Adm\Order\ar\Order::find()->where('id', $iId)->asArray()->getOne();

        if (!isset($aOrder['mail']) || empty($aOrder['mail'])) {
            throw new UserException(\Yii::t('order', 'error_no_email'));
        }

        $aGoods = ar\Goods::find()->where('id_order', $iId)->asArray()->getAll();
        if ($aGoods) {
            $aGoodsObjects = catalog\GoodsSelector::getListByIds(ArrayHelper::getColumn($aGoods, 'id_goods'), catalog\Card::DEF_BASE_CARD, false)->parse();
            $aGoodsObjects = ArrayHelper::index($aGoodsObjects, 'id');

            foreach ($aGoods as $k => $item) {
                $aGoods[$k]['object'] = isset($aGoodsObjects[$item['id_goods']]) ? $aGoodsObjects[$item['id_goods']] : null;
            }
        }
        $totalPrice = Api::getOrderSum($iId);

        $aDeliveryParameters = Api::getArrayCacheCart($aOrder['cache_cart'], $aOrder['price_delivery']);

        $sText = '';
        if ($aGoods && !empty($aGoods)) {
            $sText = $this->renderTemplate(
                'admin.goods.twig',
                ['id' => $iId,
                    'aGoods' => $aGoods,
                    'totalPrice' => $totalPrice,
                    'deliveryParameters' => $aDeliveryParameters,
                    'deliveryPrice' => $aDeliveryParameters['price_delivery'],
                    'totalPriceToPay' => ($totalPrice + $aOrder['price_delivery']),
                    'isArticle' => \skewer\build\Page\Cart\Api::isArticle(), ]
            );
        }

        $aOrder['good'] = $sText;

        $sTitle = ModulesParams::getByName('order', 'title_change_order');
        $sBody = ModulesParams::getByName('order', 'content_change_order');

        $aOptions = ['order_id' => $aOrder['id'], 'token' => $aOrder['token'], 'order_info' => $sText];

        Service::sendMail($aOrder['mail'], $sTitle, $sBody, $aOptions);

        $this->addMessage(\Yii::t('order', 'message_head_email'));
    }

    /** Получить/установить максильное число отображаемых заказов в админке */
    private static function maxOrdersOnPage()
    {
        return func_num_args() ? SysVar::set('Order.onpage_cms', (int) func_get_arg(0)) : SysVar::get('Order.onpage_cms', 100);
    }

    /**
     * @param $iId
     *
     * @throws UserException
     */
    private function updateDataDeliveryPayment($iId)
    {
        /**
         * @var ar\OrderRow
         */
        $oRow = ar\Order::find($iId);

        if (!$oRow) {
            throw new UserException("Not found [{$iId}]");
        }
        $aCacheCart = json_decode($oRow->cache_cart, true);
        $aCacheCart = ['hide_price_fractional' => $aCacheCart['hide_price_fractional']];

        if ($paidDelivery = SysVar::get(\skewer\build\Tool\DeliveryPayment\Api::PAID_DELIVERY)) {
            $oTypeDelivery = TypeDelivery::findOne(['id' => $oRow->type_delivery]);
            if (!$oTypeDelivery) {
                throw new UserException("Not found type delivery [{$oRow->type_delivery}]");
            }
            $aParametersDelivery = ['paid_delivery' => $paidDelivery];
            $totalPrice = Api::getOrderSum($iId);
            $aData['price_delivery'] = $oTypeDelivery->price;
            if ($oTypeDelivery->free_shipping && $totalPrice >= $oTypeDelivery->min_cost) {
                $aData['price_delivery'] = 0;
                $aParametersDelivery['free_shipping'] = \Yii::t('order', 'freeShipping');
            }
            if ($oTypeDelivery->coord_deliv_costs) {
                $aData['price_delivery'] = 0;
                $aParametersDelivery['coord_deliv_costs'] = \Yii::t('order', 'coordDelivCosts');
            }
            $aData['cache_cart'] = json_encode(array_merge($aParametersDelivery, $aCacheCart));
        } else {
            $aData['price_delivery'] = 0;
            $aData['cache_cart'] = json_encode($aCacheCart);
        }

        $oRow->setData($aData);
        $oRow->save();
    }
}
