<?php

namespace skewer\build\Page\WishList;

use skewer\build\Catalog\Goods\Exception;
use skewer\build\Page\WishList\ar\Wishes;
use skewer\build\Page\WishList\ar\WishListMessageModel;

/**
 * Класс работы с события Отложенных товаров
 * Class WishListEvent.
 */
class WishListEvent
{
    /** @var array Массив данных с информацией о товаре */
    public $aData;
    /** @var int Идентификатор товара в системе */
    public $iGoodsId;
    /** @var bool Флаг указывающий, что товар удалён */
    public $bRemoveGoods;
    /** @var array Контейнер с сообщениями об удалении товаров из Отложенных */
    private $aDataWishListMessage;
    /** @var string Карточка товара */
    public $extCardName;

    /**
     * Удаляет старые Отложенные и добавляет сообщения для пользователей.
     */
    final public function removeOldWishes()
    {
        $bExecuteQuery = false;

        if ($this->bRemoveGoods === true) {
            $aGoods = Wishes::find()->where(['id_goods' => $this->iGoodsId])->asArray()->all();
            if (is_array($aGoods) and count($aGoods)) {
                foreach ($aGoods as $aGoodsItem) {
                    $this->putMessageForUser($aGoodsItem['id_users'], $this->aData['title']);
                }
                $bExecuteQuery = true;
            }
            $aDelIdGoods = array_map(static function ($item) {return $item['id_goods']; }, $aGoods);
            Wishes::deleteAll(['id_goods' => $aDelIdGoods]);
        }

        if ($bExecuteQuery) {
            $this->executeQuery();
        }
    }

    /**
     * Завершающий метод вставки всех сообщений для пользователей.
     */
    private function executeQuery()
    {
        $oModel = new WishListMessageModel();

        try {
            \Yii::$app->db->createCommand()
                ->batchInsert(WishListMessageModel::tableName(), $oModel->attributes(), $this->aDataWishListMessage)
                ->execute();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Добавить сообщение для пользователя.
     *
     * @param $iIdUser
     * @param $sMessage
     */
    private function putMessageForUser($iIdUser, $sMessage)
    {
        $this->aDataWishListMessage[] = [
            'id' => 0,
            'id_users' => $iIdUser,
            'text' => $sMessage,
        ];
    }

    /**
     * Вытаскивает сообщения для пользователя и удаляет их.
     *
     * @param $iIdUser
     *
     * @return array
     */
    public static function popMessageForUser($iIdUser)
    {
        $aMessages = WishListMessageModel::findAll(['id_users' => $iIdUser]);
        WishListMessageModel::deleteAll(['id_users' => $iIdUser]);

        return $aMessages;
    }
}
