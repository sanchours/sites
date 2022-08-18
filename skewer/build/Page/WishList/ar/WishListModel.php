<?php

namespace skewer\build\Page\WishList\ar;

/**
 * This is the model class for table "wish_list".
 *
 * @property int $id
 * @property string $id_goods
 * @property int $id_users
 */
class WishListModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wish_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_goods', 'id_users'], 'required'],
            [['id_users'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_goods' => 'Id Goods',
            'id_users' => 'Id Users',
        ];
    }
}
