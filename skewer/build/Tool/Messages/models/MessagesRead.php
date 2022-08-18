<?php

namespace skewer\build\Tool\Messages\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "messages_read".
 *
 * @property int $id
 * @property int $send_id
 */
class MessagesRead extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'messages_read';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['send_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'send_id' => 'Send ID',
        ];
    }
}
