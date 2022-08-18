<?php

namespace skewer\build\Adm\Order\model;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "orders_payment".
 *
 * @property int $id
 * @property string $title
 * @property string $payment
 */
class Payments extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'payment'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['payment'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'payment' => 'Payment',
        ];
    }
}
