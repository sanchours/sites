<?php

namespace skewer\components\cart;

use skewer\build\Page\Cart\Order;
use skewer\components\auth\CurrentUser;
use skewer\components\cart\models\Cart;
use skewer\components\cart\models\CartGoods;
use yii\helpers\ArrayHelper;

/**
 * Api работы с корзиной.
 */
class Api
{
    /**
     * @var array Кеш заказов. Выполнен в виде массива, потому что
     * одному пользователю могут соответствовать два вида заказа: обычный и быстрый
     */
    private static $oCacheOrders = [];

    /**
     * Сохраняет объект заказа в бд.
     *
     * @param Order $oOrderCart Объект заказа
     * @param string $sAuthData -
     * @param $bFast bool быстрый заказ
     */
    public static function setOrderByAuthData(Order $oOrderCart, $sAuthData, $bFast)
    {
        // хеш корзины
        $sCartHash = self::buildCartHash($sAuthData, $bFast);

        // Синхронизация корзины с бд
        self::synchronization($oOrderCart, $sCartHash);
    }

    /**
     * Удалить заказ
     * - По ($sAuthData, $bFast) находим соответсвующую корзину
     * - удаляем её из бд
     * - удаляем её из кеша.
     *
     * @param string $sAuthData
     * @param bool $bFast
     */
    public static function clearOrderByAuthData($sAuthData, $bFast)
    {
        if ($bFast or $bFast === null) {
            $bFast = true;
        }

        if (!$bFast or $bFast === null) {
            $bFast = false;
        }

        $oOrder = self::getOrderByAuthData($sAuthData, $bFast);
        $oOrder->unsetAll();

        self::setOrderByAuthData($oOrder, $sAuthData, $bFast);

        $sCartHash = self::buildCartHash($sAuthData, $bFast);
        self::$oCacheOrders[$sCartHash] = null;
    }

    /**
     * Вернёт объект заказа соот-щий ($sAuthData, $bFast).
     *
     * @param string $sAuthData
     * @param bool $bFast
     *
     * @return Order
     */
    public static function getOrderByAuthData($sAuthData, $bFast)
    {
        if (!$sAuthData) {
            $oOrder = new Order();

            return $oOrder;
        }

        // хеш корзины
        $sCartHash = self::buildCartHash($sAuthData, $bFast);

        // Проверяем Order в кеше
        if (isset(self::$oCacheOrders[$sCartHash])) {
            return self::$oCacheOrders[$sCartHash];

            // Если для пользователя есть сохранённая корзина - загружаем данные из неё
        }
        if ($oCart = Cart::getByHash($sCartHash)) {
            $oOrder = new Order();
            $oOrder->loadGoodFromDb($oCart);

            self::$oCacheOrders[$sCartHash] = $oOrder;

            return $oOrder;

            // Новый заказ
        }

        $oOrder = new Order();

        self::$oCacheOrders[$sCartHash] = $oOrder;

        return $oOrder;
    }

    /**
     * Объединить корзины не залогинненого пользователя с залогинненым
     *
     * @return bool
     */
    public static function mergeCart()
    {
        if (!CurrentUser::isLoggedIn()) {
            return false;
        }

        // Корзина публичного(не залогиненного пользователя)
        $oCartPublicUser = self::getOrderByAuthData(\skewer\build\Page\Cart\Api::getCartCookie(), false);

        // Корзина залогиненного пользователя
        $oCartLogginedUser = self::getOrderByAuthData(CurrentUser::getId(), false);

        $aCartItemsPublicUser = $oCartPublicUser->getItems();

        $aCartItemsLogginedUser = $oCartLogginedUser->getItems();

        // Удаляем корзину публичного пользователя(больше не нужна)
        self::clearOrderByAuthData(\skewer\build\Page\Cart\Api::getCartCookie(), false);

        // объединяем позиции обеих корзин
        $aMergeItems = self::mergeItems($aCartItemsPublicUser, $aCartItemsLogginedUser);

        // объединенные позиции устанавливаем залогиненному пользователю
        $oCartLogginedUser->setItems($aMergeItems);

        // Записываем корзину в кеш и бд
        self::setOrderByAuthData($oCartLogginedUser, CurrentUser::getId(), false);

        return true;
    }

    /**
     * Объединение позиций двух корзин.
     *
     * @param array $aCartItems1 - позиции 1-ой корзины
     * @param array $aCartItems2 - позиции 2-ой корзины
     *
     * @return array
     */
    private static function mergeItems($aCartItems1, $aCartItems2)
    {
        // определяем пересекающиеся позиции
        $aIntersectItems = array_intersect_key($aCartItems1, $aCartItems2);

        // для пересекающихся позиции обновляем количество и итоговую сумму позиции
        foreach ($aIntersectItems as $key => &$item) {
            $item['count'] = $aCartItems1[$key]['count'] + $aCartItems2[$key]['count'];
            $item['total'] = $item['count'] * \skewer\build\Page\Cart\Api::priceFormat($item['price']);
        }

        // объединяем
        $aMergeItems = array_replace($aCartItems1, $aCartItems2, $aIntersectItems);

        return $aMergeItems;
    }

    /**
     * Синхронизация объекта заказа с БД.
     *
     * @param Order $oOrder - заказ
     * @param string $sCartHash - хеш корзины
     */
    private static function synchronization(Order $oOrder, $sCartHash)
    {
        $oCart = Cart::getExistingOrNew($sCartHash);

        // Товаров в корзине нет - удаляем запись корзины
        if (!$oOrder->getItems()) {
            $oCart->delete();

            return;
        }

        $aCartGoods = $oCart->getGoodsRelation()
            ->select(['id_goods', 'count', 'price'])
            ->asArray()
            ->all();

        // ид товаров в бд
        $aIdGoodsInDb = ArrayHelper::getColumn($aCartGoods, 'id_goods', []);

        // ид товаров в корзине
        $aIdGoodsInCart = array_keys($oOrder->getItems());

        // записи, которые надо добавить в бд
        $aItems4Add = array_diff($aIdGoodsInCart, $aIdGoodsInDb);

        foreach ($aItems4Add as $item) {
            $oRow = new CartGoods();
            $oRow->cart_id = $oCart->cart_id;
            $oRow->setAttributes($oOrder->getItemById($item));
            $oRow->save();
        }

        // записи, которые надо удалить из бд
        $aItems4Delete = array_diff($aIdGoodsInDb, $aIdGoodsInCart);

        if ($aItems4Delete) {
            $oCart->deleteGoods($aItems4Delete);
        }

        // записи, которые надо обновить в бд
        $aItems4Update = array_intersect($aIdGoodsInCart, $aIdGoodsInDb);

        $aCartGoods = ArrayHelper::index($aCartGoods, 'id_goods');

        foreach ($aItems4Update as $item) {
            $oOrderItem = $oOrder->getItemById($item);

            // Изменилось количество или цена -> обновляем
            if (($aCartGoods[$item]['count'] != $oOrderItem['count']) || ($aCartGoods[$item]['price'] != $oOrderItem['price'])) {
                $oCart->updateGood(
                    $item,
                    [
                    'price' => $oOrderItem['price'],
                    'count' => $oOrderItem['count'],
                    'total' => $oOrderItem['total'], ]
                );
            }
        }
    }

    /**
     * Построить хеш корзины.
     *
     * @param string $sAuthData - данные пользователя
     * - для авторизованного пользователя это ид пользователя
     * - для не авторизованного это кука корзины
     * @param bool $bFast - флаг, быстрого заказа
     *
     * @return string
     */
    private static function buildCartHash($sAuthData, $bFast)
    {
        $sHash = $sAuthData;

        if ($bFast) {
            $sHash .= 'fast_buy';
        }

        return $sHash;
    }

    /**
     * Получить имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }
}
