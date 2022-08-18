<?php

namespace skewer\build\Page\WishList\ar;

/**
 * This is the model class for table "wish_list_message".
 *
 * @property int $id
 * @property int $id_users
 * @property string $text
 */
class WishListMessageModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wish_list_message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_users', 'text'], 'required'],
            [['id_users'], 'integer'],
            [['text'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_users' => 'Id Users',
            'text' => 'Text',
        ];
    }
}
