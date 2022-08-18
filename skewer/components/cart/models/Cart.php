<?php

namespace skewer\components\cart\models;

/**
 * Корзина.
 *
 * @property int $cart_id
 * @property string $user_id
 * @property string $last_modified_date
 * @property int $is_auth
 */
class Cart extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['last_modified_date'], 'safe'],
            [['is_auth'], 'integer'],
            [['user_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cart_id' => 'Cart ID',
            'user_id' => 'User ID',
            'last_modified_date' => 'Date',
            'is_auth' => 'Is Auth',
        ];
    }

    /** {@inheritdoc} */
    public function beforeDelete()
    {
        CartGoods::deleteAll(['cart_id' => $this->cart_id]);

        return parent::beforeDelete();
    }

    public function getGoodsRelation()
    {
        return $this->hasMany(CartGoods::className(), ['cart_id' => 'cart_id']);
    }

    /**
     * Получить корзину по хешу.
     *
     * @param string $sHash - хеш корзины
     *
     * @return self
     */
    public static function getByHash($sHash)
    {
        return Cart::findOne(['user_id' => $sHash]);
    }

    /**
     * Получить существующую или создать новую корзину по хешу.
     *
     * @param string $sCartHash - хеш корзины
     *
     * @return self
     */
    public static function getExistingOrNew($sCartHash)
    {
        $oCart = Cart::getByHash($sCartHash);

        if (!$oCart) {
            $oCart = Cart::createByHash($sCartHash);
        }

        return $oCart;
    }

    /**
     * Создание новой корзины по хешу.
     *
     * @param string $sHash - хеш корзины
     *
     * @return self
     */
    public static function createByHash($sHash)
    {
        $cart = new Cart();
        $cart->user_id = (string) $sHash;
        $cart->last_modified_date = date('Y-m-d H:i:s');
        $cart->save();

        return $cart;
    }

    /**
     * Обновление даты модификации корзины.
     *
     * @param int $iCartId - ид корзины
     */
    public static function updateLastModifiedDate($iCartId)
    {
        $oCart = Cart::findOne(['cart_id' => $iCartId]);

        if ($oCart) {
            $oCart->last_modified_date = date('Y-m-d H:i:s');
            $oCart->save();
        }
    }

    /**
     * Удаление товаров корзины.
     *
     * @param mixed $aGoodsId - ид/список ид товаров корзины, которые надо удалить
     */
    public function deleteGoods($aGoodsId)
    {
        CartGoods::deleteAll(['cart_id' => $this->cart_id, 'id_goods' => $aGoodsId]);

        Cart::updateLastModifiedDate($this->cart_id);
    }

    /**
     * Обновление товаров корзины.
     *
     * @param mixed $mGoodId - ид/список ид товаров корзины, которые надо обновить
     * @param array $aData - данные для обновления
     */
    public function updateGood($mGoodId, $aData)
    {
        CartGoods::updateAll($aData, ['cart_id' => $this->cart_id, 'id_goods' => $mGoodId]);

        Cart::updateLastModifiedDate($this->cart_id);
    }
}
