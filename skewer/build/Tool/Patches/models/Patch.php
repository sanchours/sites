<?php

namespace skewer\build\Tool\Patches\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "patches".
 *
 * @property string $patch_uid
 * @property string $install_date
 * @property string $file
 * @property string $description
 */
class Patch extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'patches';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['patch_uid', 'file', 'description'], 'required'],
            [['install_date'], 'safe'],
            [['patch_uid', 'file', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'patch_uid' => 'Patch Uid',
            'install_date' => 'Install Date',
            'file' => 'File',
            'description' => 'Description',
        ];
    }
}
