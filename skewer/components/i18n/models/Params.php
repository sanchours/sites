<?php

namespace skewer\components\i18n\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * Модель для параметров модулей.
 *
 * @property int $id
 * @property string $module
 * @property string $name
 * @property string $value
 * @property string $language
 */
class Params extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'modules_params';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module', 'name', 'language'], 'required'],
            [['module'], 'unique', 'targetAttribute' => ['module', 'name', 'language']],
            [['module', 'name'], 'string', 'max' => 64],
            [['value'], 'string'],
            [['language'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module' => 'Module',
            'name' => 'Name',
            'value' => 'Value',
            'language' => 'Language',
        ];
    }
}
