<?php

namespace skewer\components\i18n\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * Модель для системных разделов
 * This is the model class for table "ServiceSections".
 *
 * @property int $id
 * @property string $name имя. Уникальность по полям $name и $language
 * @property int $value значение
 * @property string $language язык
 * @property string $title описание
 */
class ServiceSections extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ServiceSections';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'language', 'title', 'value'], 'required'],
            [['name', 'language'], 'unique', 'targetAttribute' => ['name', 'language']],
            [['value'], 'integer'],
            [['name'], 'string', 'max' => 128],
            [['language'], 'string', 'max' => 10],
            [['title'], 'string', 'max' => 255],
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
            'value' => 'Value',
            'language' => 'Language',
            'title' => 'Title',
        ];
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new ServiceSections();

        $oRow->language = 'ru';
        $oRow->name = '';
        $oRow->title = '';
        $oRow->value = '';

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }
}
