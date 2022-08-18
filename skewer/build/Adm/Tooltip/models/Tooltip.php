<?php

namespace skewer\build\Adm\Tooltip\models;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $name
 * @property string $text
 */
class Tooltip extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tooltips';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'text'], 'required'],
            [['name', 'text'], 'string'],
            [['text'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'text' => 'text',
        ];
    }

    public static function getDefaultValues()
    {
        $aValues = [
            'id' => 0,
            'name' => '',
            'text' => '',
        ];

        return $aValues;
    }
}
