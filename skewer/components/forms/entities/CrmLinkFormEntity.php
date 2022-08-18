<?php

declare(strict_types=1);

namespace skewer\components\forms\entities;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "crm_link_form".
 *
 * @property int $id
 * @property string $crm_field_alias
 * @property int $form_id
 * @property int $field_id
 */
class CrmLinkFormEntity extends ActiveRecord
{
    const CRM_FIELDS = [
        'deal_title' => 'Заголовок сделки',
        'deal_content' => 'Содержание сделки',
        'contact_client' => 'Имя клиента',
        'contact_email' => 'E-mail клиента',
        'contact_phone' => 'Телефон клиента',
        'contact_mobile' => 'Телефон клиента мобильный',
        'deal_event' => 'Событие',
        'deal_type' => 'Тип сделки',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crm_link_form';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['crm_field_alias', 'form_id'], 'required'],
            [['form_id', 'field_id'], 'integer'],
            [['crm_field_alias'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'crm_field_alias' => 'Crm Field Alias',
            'form_id' => 'Form ID',
            'field_id' => 'Field ID',
        ];
    }

    public static function hasCreatedFields(int $idForm)
    {
        $field = self::findOne(['form_id' => $idForm]);

        return $field instanceof CrmLinkFormEntity;
    }

    public static function createDefaultFieldsByForm(int $idForm)
    {
        foreach (self::CRM_FIELDS as $alias => $title) {
            $field = new static();
            $field->crm_field_alias = $alias;
            $field->form_id = $idForm;
            $field->save();
        }
    }

    public static function getFieldsByIdForm(int $idForm)
    {
        return self::find()->where(['form_id' => $idForm])->all();
    }

    public static function getFieldsById(int $id)
    {
        return self::findOne($id);
    }
}
