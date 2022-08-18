<?php

namespace skewer\build\Page\Cart;

use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\base\Twig;
use skewer\components;
use skewer\components\auth\CurrentUser;
use yii\helpers\ArrayHelper;

/**
 * Методы для работы с заказом корзины
 * Class Api.
 */
class Api
{
    const PRICE_FIELDNAME = 'price';

    /**
     * Форматирование вывода копеек.
     *
     * @param $mPrice
     *
     * @return float|int
     */
    public static function priceFormat($mPrice)
    {
        return (SysVar::get('catalog.hide_price_fractional')) ? (int) round($mPrice) : (float) $mPrice;
    }

    /**
     * Форматирование вывода копеек для запросов через ajax.
     *
     * @param $mPrice
     *
     * @return float|int
     */
    public static function priceFormatAjax($mPrice)
    {
        return Twig::priceFormat($mPrice);
    }

    /**
     * Добавление торава в корзину
     * Если уже есть, количество складывается.
     *
     * @param $iObjectId int ID товара
     * @param $iCount int Количество
     * @param $bFast bool быстрый заказ
     *
     * @return bool true в случае успеха и false если превышено максимальное число заказа
     */
    public static function setItem($iObjectId, $iCount, $bFast = false)
    {
        $oOrderCart = self::getOrder($bFast);
        $aCartItem = $oOrderCart->getExistingOrNew($iObjectId);
        if (!$aCartItem) {
            return true;
        } // Ошибочно задан объект заказа

        // Если в заказ добавляется новая позиция и не позволяет предел, то отменить
        if (!$aCartItem['count'] and (count($oOrderCart->getItems()) >= self::maxOrderSize())) {
            return false;
        }

        // Актуализируем цену товара в заказе
        $aGood = components\catalog\GoodsSelector::get($iObjectId);

        if ($aGood) {
            $aCartItem['price'] = ArrayHelper::getValue($aGood, 'fields.price.value', '');
        }

        $aCartItem['count'] += $iCount;
        $aCartItem['total'] = $aCartItem['count'] * self::priceFormat($aCartItem['price']);

        $oOrderCart->setItem($aCartItem);

        self::setOrder($oOrderCart, $bFast);

        return true;
    }

    /**
     * Вернет данные для построения хеша корзины.
     *
     * @return string
     */
    public static function getCurrentUserAuthData()
    {
        if (CurrentUser::isLoggedIn()) {
            return CurrentUser::getId();
        }
        $userHash = self::getCartCookie();

        return $userHash;
    }

    /**
     * Сохраняет объект заказа в сессию.
     *
     * @param Order $oOrderCart Объект заказа
     * @param $bFast bool быстрый заказ
     */
    public static function setOrder(Order $oOrderCart, $bFast = false)
    {
        $sAuthData = self::getCurrentUserAuthData();

        components\cart\Api::setOrderByAuthData($oOrderCart, $sAuthData, $bFast);
    }

    /**
     * Очищает корзину заказов текущего пользователя.
     *
     * @param $bFast bool быстрый заказ. Если не указан, то очищает полностью
     */
    public static function clearOrder($bFast = null)
    {
        $sAuthData = self::getCurrentUserAuthData();

        components\cart\Api::clearOrderByAuthData($sAuthData, $bFast);
    }

    /**
     * Возвращает объект заказа.
     *
     * @param $bFast bool быстрый заказ
     *
     * @return Order
     */
    public static function getOrder($bFast = false)
    {
        if (!CurrentUser::isLoggedIn()) {
            // Устанавливаем/обновляем куку, если её нет или истекла
            if (!self::checkCartCookie()) {
                self::setCartCookie();
            }
        }

        $sAuthData = self::getCurrentUserAuthData();

        $oOrder = components\cart\Api::getOrderByAuthData($sAuthData, $bFast);

        return $oOrder;
    }

    /** Получить/установить максимальное число позиций в одном заказе */
    public static function maxOrderSize()
    {
        return func_num_args() ? SysVar::set('Order.order_max_size', (int) func_get_arg(0)) : SysVar::get('Order.order_max_size', 500);
    }

    /** Получить/установить максильное число отображаемых заказов в личном кабинете */
    public static function maxOrdersOnPage()
    {
        return  func_num_args() ? SysVar::set('Order.onpage_profile', (int) func_get_arg(0)) : SysVar::get('Order.onpage_profile', 10);
    }

    /**
     * Отсылает заказ в JSON.
     *
     * @param $bFast bool быстрый заказ
     * @param $aParamsAdded array Дополнительные параметры
     */
    public static function sendJSON($bFast = false, array $aParamsAdded = [])
    {
        $aOrderCart = self::getOrder($bFast);

        echo json_encode(
            [
                'lastItem' => $aOrderCart->getItemLast(),
                'count' => $aOrderCart->getTotalCount(),
                'total' => self::priceFormatAjax($aOrderCart->getTotalPrice()),
            ] + $aParamsAdded
        );
        exit;
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

    /**
     * Получить куку корзины для текущего пользователя.
     *
     * @return string | false
     */
    public static function getCartCookie()
    {
        return \Yii::$app->getRequest()->getCookies()->getValue('userHash', false);
    }

    /**
     * Проверить куку корзины(существует/не истекла).
     *
     * @return bool
     */
    public static function checkCartCookie()
    {
        return \Yii::$app->getRequest()->getCookies()->has('userHash');
    }

    /**
     * Установить куку корзины для текущего пользователя.
     */
    public static function setCartCookie()
    {
        $userHash = hash('sha512', time() . '#*#BaraNeKontritsya322#*#' . session_id());
        $cookieResponse = \Yii::$app->getResponse()->getCookies();

        $cookieResponse->readOnly = false;

        $cookieResponse->add(new \yii\web\Cookie([
            'name' => 'userHash',
            'value' => $userHash,
            'expire' => time() + 60 * 60 * 24 * 30,
            'domain' => '.' . Site::domain()
        ]));
    }

    public static function isArticle()
    {
        $card = components\catalog\Card::getId(components\catalog\Card::DEF_BASE_CARD);

        if ($card) {
            $isArticle = \Yii::$app->db->createCommand("SELECT `value` FROM `c_field_attr` INNER JOIN `c_field` ON `c_field_attr`.`field` = `c_field`.`id` WHERE `c_field`.`entity`= 1 and `c_field`.`name`= 'article' and `c_field_attr`.`tpl` = 'active'")->queryOne();
        }

        return  isset($isArticle['value']) ? (bool) $isArticle['value'] : false;
    }

    /**
     * Получить урл страницы оформления заказа.
     *
     * @param int $iSectionId - ид раздела "Корзина"
     *
     * @return string
     */
    public static function getUrlCheckoutPage($iSectionId)
    {
        $sStr = sprintf('[%d][Cart?action=checkout]', $iSectionId);

        return \Yii::$app->router->rewriteURL($sStr);
    }

    /**
     * Получить урл страницы завершения оформления заказа.
     *
     * @param int $iSectionId - ид раздела "Корзина"
     *
     * @return string
     */
    public static function getUrlDonePage($iSectionId)
    {
        $sStr = sprintf('[%d][Cart?action=done]', $iSectionId);

        return \Yii::$app->router->rewriteURL($sStr);
    }
}
