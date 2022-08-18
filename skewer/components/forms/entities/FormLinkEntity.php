<?php

declare(strict_types=1);

namespace skewer\components\forms\entities;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "form_link".
 * взаимосвязь с каталогом
 *
 * @property int $link_id
 * @property int $form_id
 * @property string $form_field
 * @property string $card_field
 */
class FormLinkEntity extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'form_link';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['form_id', 'form_field', 'card_field'], 'required'],
            [['form_id'], 'integer'],
            [['form_field', 'card_field'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'link_id' => 'Link ID',
            'form_id' => 'Form ID',
            'form_field' => 'Form Field',
            'card_field' => 'Card Field',
        ];
    }

    public static function getLinksByIdForm(int $idForm)
    {
        return self::find()->where(['form_id' => $idForm])->all();
    }
}
