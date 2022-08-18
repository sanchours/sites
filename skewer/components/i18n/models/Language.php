<?php

namespace skewer\components\i18n\models;

use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\import\field\Active;

/**
 * This is the model class for table "language".
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $icon
 * @property string $src_lang
 * @property int $active
 * @property int $admin
 */
class Language extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'title'], 'required'],
            [['active', 'admin'], 'integer'],
            [['name', 'src_lang'], 'string', 'max' => 30],
            [['title'], 'string', 'max' => 64],
            [['icon'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'title' => 'Title',
            'icon' => 'Icon',
            'src_lang' => 'Src Lang',
            'active' => 'Active',
            'admin' => 'Admin',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if ($this->active) {
            $this->addError('active', \Yii::t('languages', 'error_lang_is_active'));

            return false;
        }

        return parent::beforeDelete();
    }

    public function delete()
    {
        return parent::delete();
    }
}
