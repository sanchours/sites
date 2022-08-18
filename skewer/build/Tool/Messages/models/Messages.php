<?php

namespace skewer\build\Tool\Messages\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "messages".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property int $type
 * @property bool $new
 * @property string $arrival_date
 * @property int $send_id
 * @property int $send_read
 */
class Messages extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'messages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'text', 'type', 'send_id'], 'required'],
            [['text'], 'string'],
            [['type', 'send_id', 'send_read'], 'integer'],
            [['new'], 'boolean'],
            [['arrival_date'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('messages', 'field_id'),
            'title' => \Yii::t('messages', 'field_title'),
            'text' => \Yii::t('messages', 'field_text'),
            'type' => \Yii::t('messages', 'field_status'),
            'new' => \Yii::t('messages', 'field_new'),
            'arrival_date' => \Yii::t('messages', 'field_date'),
            'send_id' => \Yii::t('messages', 'field_send_id'),
            'send_read' => \Yii::t('messages', 'field_sendread'),
        ];
    }
}
