<?php
/**
 * Created by PhpStorm.
 * User: ermak
 * Date: 28.06.2017
 * Time: 10:54.
 */

namespace skewer\build\Page\WishList\ar;

use skewer\base\orm\Query;
use yii\helpers\ArrayHelper;

class Wishes extends WishListModel
{
    public static function getNewRow($aData = [])
    {
        $oRow = new Wishes();

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * Список отложенных товаров пользователя.
     *
     * @param int $id Идентификатор пользователя
     * @param array $limit ограничения на загрузку
     *
     * @return array
     */
    public static function getWishList($id, $limit)
    {
        $aWishList = Wishes::find()
            ->where(['id_users' => $id])
            ->limit($limit['onpage'])
            ->offset(($limit['page'] - 1) * $limit['onpage'])
            ->asArray()
            ->all();

        return $aWishList;
    }

    /**
     * Возвращает сумму цен по id товаров или false, если не все товары из списка активны.
     *
     * @param $id int|array
     *
     * @return bool|float
     */
    private static function getTotalPricesById($id)
    {
        if (is_array($id)) {
            $aRes = Query::SelectFrom('co_base_card')
                ->fields(['price'])
                ->where('id IN ?', $id)
                ->andWhere('active', 1)
                ->andWhere('buy', 1)
                ->asArray()
                ->getAll();
        } else {
            $aRes = Query::SelectFrom('co_base_card')
                ->fields(['price'])
                ->where('id', $id)
                ->andWhere('active', 1)
                ->andWhere('buy', 1)
                ->asArray()
                ->getAll();
        }

        if (count($aRes) === count($id)) {
            return (float) array_sum(ArrayHelper::getColumn($aRes, 'price', false));
        }

        return false;
    }

    /**
     * Проверяет наличие товара в списке отложенных товаров пользователя.
     *
     * @param int $idUser Идентификатор пользователя
     * @param int $idGoods Идентификатор товара
     *
     * @return bool
     */
    public static function existInWishList($idGoods, $idUser)
    {
        return Wishes::find()->where(['id_users' => $idUser, 'id_goods' => $idGoods])->count();
    }

    /**
     * Добавление товара в список отложенных пользователя.
     *
     * @param int $idUser Идентификатор пользователя
     * @param int $idGoods Идентификатор товара
     *
     * @return bool
     */
    public static function addInWishList($idGoods, $idUser)
    {
        $oRow = new Wishes();
        $oRow->setAttributes([
                'id_goods' => $idGoods,
                'id_users' => $idUser,
            ]);

        if (!$oRow->save()) {
            return false;
        }

        return true;
    }

    /**
     * Удаление товара из списка пользователя.
     *
     * @param int $idUser Идентификатор пользователя
     * @param int $idGoods Идентификатор товара
     *
     * @return bool
     */
    public static function delFromWishList($idGoods, $idUser)
    {
        $oRow = Wishes::findOne(['id_users' => $idUser, 'id_goods' => $idGoods]);
        if ($oRow->delete()) {
            return true;
        }

        return false;
    }

    /**
     * Очистка списка пользователя.
     *
     * @param int $idUser Идентификатор пользователя
     *
     * @return bool
     */
    public static function resetWishList($idUser)
    {
        return Wishes::deleteAll(['id_users' => $idUser]);
    }

    /**
     * Получение количества товаров в списке пользователя.
     *
     * @param int $idUser Идентификатор пользователя
     *
     * @return bool
     */
    public static function getCountItems($idUser)
    {
        return Wishes::find()->where(['id_users' => $idUser])->count();
    }
}
