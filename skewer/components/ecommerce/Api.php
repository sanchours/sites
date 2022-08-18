<?php

namespace skewer\components\ecommerce;

use skewer\base\ft\Cache;
use skewer\base\orm\Query;
use skewer\base\section\Tree;
use skewer\base\site_module\Parser;
use skewer\build\Adm\Order\ar\Goods;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsSelector;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Api
{
    /**
     * Вернет js-код отсылающий ecommerce данные о совершении покупки.
     *
     * @param int $iOrderId - ид, выполненного заказа
     * @param bool|int $iSectionId - ид раздела из которого выполнена покупка(!!!только для покупки в один клик)
     * @param bool $bIsFastBuy - покупка выполнена через форму "Купить в один клик" ?
     *
     * @return string
     * */
    public static function buildScriptPurchase($iOrderId, $iSectionId = false, $bIsFastBuy = false)
    {
        $aGoodsOrder = Goods::getByOrderId($iOrderId);

        if (!$aGoodsOrder) {
            return '';
        }

        $aEcommerceObjects = $bIsFastBuy
            ? self::getEcommerceData4GoodsOrder($aGoodsOrder, $iSectionId, 'Форма купить в один клик')
            : self::getEcommerceData4GoodsOrder($aGoodsOrder, false, false);

        $aData = [
            'ecommerce_objects' => $aEcommerceObjects,
            'order_id' => $iOrderId,
            'bIsFastBuy' => $bIsFastBuy,
            'AllowEcommerceSendData' => true,
        ];

        $sHtml = Parser::parseTwig('purchase.twig', $aData, __DIR__ . '/templates');

        return $sHtml;
    }

    /**
     * Добавит ecommerce-данные в массив товаров.
     *
     * @param  array $aGoods - массив товаров
     * @param  bool|int $iSectionId - id раздела, в котором будут выводиться данные товары. При =false - значение category ec-данных рассчитываться не будет
     * @param  string $sListTitle - название списка, в котором будут выводиться данные товары
     * @param  bool $json - отдать данные в json-формате?
     *
     * @return array
     */
    public static function addEcommerceDataInGoods($aGoods, $iSectionId, $sListTitle, $json = true)
    {
        $aGoods = ArrayHelper::index($aGoods, 'id');
        $aEcommerceObjects = self::getEcommerceDataByListGoods($aGoods, $iSectionId, '', $sListTitle, $json);

        foreach ($aGoods as $iGoodId => &$aGood) {
            if (isset($aEcommerceObjects[$iGoodId])) {
                $aGood['ecommerce'] = $aEcommerceObjects[$iGoodId];
            }
        }

        return $aGoods;
    }

    /**
     * Добавит ecommerce данные в товары заказа.
     *
     * @param array $aGoodsOrder - товары заказа
     *
     * @return array
     */
    public static function addEcommerceDataInGoodsOrder($aGoodsOrder)
    {
        $aIds = ArrayHelper::getColumn($aGoodsOrder, 'id_goods', []);
        $aEcommerceObjects = self::getEcommerceDataByGoodIds($aIds, false, '', false);

        foreach ($aGoodsOrder as &$aItem) {
            $iGoodId = $aItem['id_goods'];
            if (isset($aEcommerceObjects[$iGoodId])) {
                $aItem['ecommerce'] = $aEcommerceObjects[$iGoodId];
            }
        }

        return $aGoodsOrder;
    }

    /**
     * Вернет json-закодированный массив ecommerce данных для товаров заказа.
     *
     * @param array $aGoodsOrder - товары заказа(структура массива совпадает с таблицей orders_goods)
     * @param  bool|int $iSectionId - id раздела, в котором будут выводиться данные товары. При =false - значение category ec-данных рассчитываться не будет
     * @param  string $sListTitle - название списка, в котором будут выводиться данные товары
     *
     * @return array|string
     */
    private static function getEcommerceData4GoodsOrder($aGoodsOrder, $iSectionId, $sListTitle)
    {
        $aIds = ArrayHelper::getColumn($aGoodsOrder, 'id_goods', []);

        $aEcommerceObjects = self::getEcommerceDataByGoodIds($aIds, $iSectionId, '', $sListTitle, false);

        if ($aEcommerceObjects === false) {
            return [];
        }

        $aGoodsOrder = ArrayHelper::index($aGoodsOrder, 'id_goods');

        // Добавляем инфор-ю о количестве купленных товаров
        foreach ($aEcommerceObjects as $iGoodId => &$aEcommerceObject) {
            if (isset($aGoodsOrder[$iGoodId]['count'])) {
                $aEcommerceObject['quantity'] = (int) $aGoodsOrder[$iGoodId]['count'];
            }
        }

        // Переиндексируем
        $aEcommerceObjects = array_values($aEcommerceObjects);

        return Json::encode($aEcommerceObjects);
    }

    /**
     * Добавит ecommerce-данные в массив товаров коллекции.
     *
     * @param  array $aGoods - массив товаров
     * @param  bool|int $iSectionId - id раздела, в котором будут выводиться данные товары. При =false - значение category ec-данных рассчитываться не будет
     * @param  string $sElementCollectionTitle - название элемента коллекции
     * @param  string $sListTitle - название списка, в котором будут выводиться данные товары
     * @param  bool $json - отдать данные в json-формате?
     *
     * @return array
     */
    public static function addEcommerceDataInGoodsCollection($aGoods, $iSectionId, $sElementCollectionTitle, $sListTitle, $json = true)
    {
        $aGoods = ArrayHelper::index($aGoods, 'id');
        $aEcommerceObjects = self::getEcommerceDataByListGoods($aGoods, $iSectionId, $sElementCollectionTitle, $sListTitle, $json);

        foreach ($aGoods as $iGoodId => &$aGood) {
            if (isset($aEcommerceObjects[$iGoodId])) {
                $aGood['ecommerce'] = $aEcommerceObjects[$iGoodId];
            }
        }

        return $aGoods;
    }

    /**
     * Вернет ecommerce-данные товаров по массиву их идентификаторов.
     *
     * @param  array $aIds - массив id товаров
     * @param  bool|int $iSectionId - id раздела, в котором будут выводиться данные товары. При =false - значение category ec-данных рассчитываться не будет
     * @param  string $sElementCollectionTitle - название элемента коллекции
     * @param  string $sListTitle - название списка, в котором будут выводиться данные товары
     * @param  bool $json - отдать данные в json-формате?
     *
     * @return array|bool
     */
    private static function getEcommerceDataByGoodIds($aIds, $iSectionId, $sElementCollectionTitle = '', $sListTitle, $json = true)
    {
        if (!$aIds) {
            return false;
        }

        $aGoods = GoodsSelector::getListByIds($aIds, Card::DEF_BASE_CARD, false)
            ->parse();

        if (!$aGoods) {
            return false;
        }

        return self::getEcommerceDataByListGoods($aGoods, $iSectionId, $sElementCollectionTitle, $sListTitle, $json);
    }

    /**
     * Получить ecommerce-данные по списку товаров.
     *
     * @param  array $aListGoods - массив товаров
     * @param  bool|int $iSectionId - id раздела, в котором будут выводиться данные товары. При =false - значение category ec-данных рассчитываться не будет
     * @param  string $sElementCollectionTitle - название элемента коллекции
     * @param  string $sListTitle - название списка, в котором будут выводиться данные товары
     * @param  bool $json - отдать данные в json-формате?
     *
     * @return array
     */
    private static function getEcommerceDataByListGoods($aListGoods, $iSectionId, $sElementCollectionTitle = '', $sListTitle, $json = true)
    {
        if (!$aListGoods) {
            return $aListGoods;
        }

        // идентфикаторы базовых товаров
        $aIdsBaseGoods = array_unique(ArrayHelper::getColumn($aListGoods, 'main_obj_id'));

        $aBaseGoods = self::getBaseRowGoodsByIds($aIdsBaseGoods);

        // id -> title баз.товаров
        $aTitlesBaseGood = ArrayHelper::map($aBaseGoods, 'id', 'title');

        $iGoodsPositionInList = 1;
        $aEcommerceObjects = [];
        foreach ($aListGoods as $aGoodItem) {
            $bIsModification = ($aGoodItem['main_obj_id'] != $aGoodItem['id']);

            $aEcommerceData = [
                'id' => $aGoodItem['main_obj_id'],
                'name' => htmlspecialchars($aTitlesBaseGood[$aGoodItem['main_obj_id']]),
                'price' => ArrayHelper::getValue($aGoodItem, 'fields.price.value', 0),
                'variant' => $bIsModification ? ArrayHelper::getValue($aGoodItem, 'alias', '') : 'Основной товар',
            ];

            if ($iSectionId !== false) {
                $sDelimiter = '/';
                $sCategory = Tree::getChainSectionsToCurrentPage($iSectionId, true, $sDelimiter);

                // Заголовок елемента коллекции
                if ($sElementCollectionTitle) {
                    $sCategory = $sCategory . $sDelimiter . $sElementCollectionTitle;
                }

                $aEcommerceData += [
                    'category' => htmlspecialchars($sCategory),
                ];
            }

            if ($sListTitle !== false) {
                $aEcommerceData += [
                    'list' => $sListTitle,
                    'position' => $iGoodsPositionInList++,
                ];
            }

            $aEcommerceObjects[$aGoodItem['id']] = ($json)
                ? Json::htmlEncode($aEcommerceData)
                : $aEcommerceData;
        }

        return $aEcommerceObjects;
    }

    /**
     * По массиву id вернет базовые строки товаров(из таблицы co_base_card).
     *
     * @param array $aIds
     *
     * @return array
     */
    private static function getBaseRowGoodsByIds($aIds)
    {
        $oModel = Cache::get(Card::DEF_BASE_CARD);

        $aBaseGoods = Query::SelectFrom($oModel->getTableName(), $oModel)
            ->where($oModel->getPrimaryKey(), $aIds)
            ->asArray()
            ->getAll();

        return $aBaseGoods;
    }
}
