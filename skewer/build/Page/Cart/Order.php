<?php

namespace skewer\build\Page\Cart;

use skewer\base\SysVar;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use skewer\components\cart\models\Cart;
use skewer\components\cart\models\CartGoods;
use skewer\components\catalog;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с заказом корзины
 * Class Order.
 */
class Order
{
    /** Массив позиций заказа */
    private $items = [];

    /** Id последней заказанной/изменённой позиции корзины */
    private $lastItem = 0;

    private $iTotalPrice = 0;

    private $aTypeDelivery = [];

    private $bPaidDelivery = false;

    public function __construct()
    {
        $this->bPaidDelivery = SysVar::get(\skewer\build\Tool\DeliveryPayment\Api::PAID_DELIVERY);

        if ($this->bPaidDelivery) {
            $aTypeDelivery = TypeDelivery::find()
                ->orderBy('priority')
                ->where(['active' => true])
                ->asArray()
                ->one();
            $this->aTypeDelivery = $aTypeDelivery;
        }
    }

    /**
     * @return array
     */
    public function getTypeDelivery()
    {
        return $this->aTypeDelivery;
    }

    /**
     * @param array $aTypeDelivery
     */
    public function setTypeDelivery($aTypeDelivery)
    {
        $this->aTypeDelivery = $aTypeDelivery;
    }

    /**
     * @return null|bool|string
     */
    public function getPaidDelivery()
    {
        return $this->bPaidDelivery;
    }

    /**
     * @return int
     */
    public function getPriceDelivery()
    {
        $aTypeDelivery = $this->getTypeDelivery();

        if (!$aTypeDelivery
            || $this->getCoordDelivCosts()
            || $this->getFreeShipping()) {
            return 0;
        }

        return Api::priceFormat($aTypeDelivery['price']);
    }

    public function getDeliveryForPage()
    {
        if ($this->getFreeShipping()) {
            return \Yii::t('order', 'freeShipping');
        }

        if ($this->getCoordDelivCosts()) {
            return \Yii::t('order', 'coordDelivCosts');
        }

        return $this->getPriceDelivery();
    }

    public function getFreeShipping()
    {
        $aTypeDelivery = $this->getTypeDelivery();

        return $aTypeDelivery
            && $aTypeDelivery['free_shipping']
            && $this->getTotalPrice() >= $aTypeDelivery['min_cost'];
    }

    public function getCoordDelivCosts()
    {
        $aTypeDelivery = $this->getTypeDelivery();

        return $aTypeDelivery['coord_deliv_costs'] ?? '';
    }

    /**
     * Возвращает существующую позицию или новую.
     *
     * @param $iObjectId int ID объекта
     *
     * @return array|bool
     */
    public function getExistingOrNew($iObjectId)
    {
        if (isset($this->items[$iObjectId])) {
            return $this->items[$iObjectId];
        }

        $aGood = catalog\GoodsSelector::get($iObjectId, 1);

        return (!$aGood) ? false : [
            'id_goods' => $iObjectId,
            'card' => $aGood['card'],
            'url' => $aGood['url'],
            'title' => $aGood['title'],
            'article' => ArrayHelper::getValue($aGood, 'fields.article.value', ''),
            'image' => ArrayHelper::getValue($aGood, 'fields.gallery.first_img.images_data', ''),
            'count' => 0,
            'price' => Api::priceFormat(ArrayHelper::getValue($aGood, 'fields.price.value', '')),
            'total' => 0,
        ];
    }

    /**
     * Устанавливает позицию заказа.
     *
     * @param array $aItem Позиция заказа
     */
    public function setItem(array $aItem)
    {
        $this->items[$aItem['id_goods']] = $aItem;
        $this->lastItem = $aItem['id_goods'];
    }

    /**
     * Удаляет позицию заказа.
     *
     * @param $itemId int ID позиции
     */
    public function unsetItem($itemId)
    {
        if (isset($this->items[$itemId])) {
            unset($this->items[$itemId]);
        }
    }

    /**
     * Возвращает массив позиций заказа. Точнее указатель на массив (сделано для оптимизации, иначе создаётся копия массива).
     *
     * @return array Позиции заказа
     */
    public function &getItems()
    {
        return $this->items;
    }

    public function setItems(array $aItems)
    {
        $this->items = $aItems;
    }

    /**
     * Возвращает число позиций заказа.
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->items);
    }

    public function setTotalPrice()
    {
        $this->iTotalPrice = 0;

        foreach ($this->items as &$aItem) {
            $this->iTotalPrice += $aItem['total'];
        }

        return Api::priceFormat($this->iTotalPrice);
    }

    /**
     * Возвращает общую сумму заказа.
     *
     * @return int
     */
    public function getTotalPrice()
    {
        if ($this->iTotalPrice == 0) {
            return $this->setTotalPrice();
        }

        return Api::priceFormat($this->iTotalPrice);
    }

    /**
     * Возвращает общую сумму заказа c доставкой.
     *
     * @return int
     */
    public function getTotalPriceToPay()
    {
        if ($this->iTotalPrice == 0) {
            $this->setTotalPrice();
        }

        return Api::priceFormatAjax(Api::priceFormat($this->iTotalPrice + $this->getPriceDelivery()));
    }

    /**
     * Возвращает позицию по ID, а именно прямую ссылку.
     *
     * @param $id int ID позиции
     *
     * @return array|bool
     */
    public function getItemById($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : false;
    }

    /**
     * Удаляет все позиции заказа.
     */
    public function unsetAll()
    {
        $this->items = [];
    }

    /**
     * Возвращает общее количество товаров с учетом количества.
     *
     * @return int
     */
    public function getTotalCount()
    {
        $iCount = 0;

        foreach ($this->items as &$aItem) {
            $iCount += $aItem['count'];
        }

        return $iCount;
    }

    /**
     * Возвращает последнюю позицию в виде массива.
     *
     * @return array
     */
    public function getItemLast()
    {
        if (!isset($this->items[$this->lastItem])) {
            return [];
        }

        $aItem = $this->items[$this->lastItem];

        return [
            'id_goods' => $aItem['id_goods'],
            'title' => $aItem['title'],
            'count' => $aItem['count'],
            'price' => Api::priceFormatAjax($aItem['price']),
            'total' => Api::priceFormatAjax($aItem['total']),
        ];
    }

    /**
     * Загружает товары корзины из бд в текущий объект
     *
     * @param Cart $oCart - корзина
     */
    public function loadGoodFromDb(Cart $oCart)
    {
        /** @var CartGoods $item */
        foreach ($oCart->getGoodsRelation()->each() as $item) {
            $item->image = json_decode($item->image);
            $this->setItem($item->getAttributes(null, ['id', 'cart_id']));
        }
    }
}
