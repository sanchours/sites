<?php

namespace skewer\components\cart\models;

/**
 * Позиции корзины.
 *
 * @property int $id
 * @property int $cart_id
 * @property int $id_goods
 * @property string $card
 * @property string $url
 * @property string $title
 * @property string $article
 * @property string $image
 * @property int $count
 * @property float $price
 * @property float $total
 */
class CartGoods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart_goods';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cart_id', 'id_goods', 'count'], 'integer'],
            [['price', 'total'], 'number'],
            [['card', 'url', 'title', 'article', 'image'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cart_id' => 'Cart ID',
            'id_goods' => 'Id Goods',
            'card' => 'Card',
            'url' => 'Url',
            'title' => 'Title',
            'article' => 'Article',
            'count' => 'Count',
            'price' => 'Price',
            'total' => 'Total',
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->image = json_encode($this->image);

        return parent::save($runValidation, $attributeNames);
    }

    public function afterSave($insert, $changedAttributes)
    {
        Cart::updateLastModifiedDate($this->cart_id);

        parent::afterSave($insert, $changedAttributes);
    }
}
