<?php

namespace skewer\components\redirect\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "redirect301".
 *
 * @property int $id
 * @property string $old_url
 * @property string $new_url
 */
class Redirect extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'redirect301';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['old_url', 'new_url'], 'required'],
            [['old_url', 'new_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'old_url' => \Yii::t('redirect301', 'old_url'),
            'new_url' => \Yii::t('redirect301', 'new_url'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /*Добавление приоритета*/
        if ($insert) {
            $this->setAttribute('priority', $this->getAttribute('id'));
            $this->save();
        }
    }
}
