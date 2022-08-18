<?php

declare(strict_types=1);

namespace skewer\components\forms\entities;

use skewer\components\forms\entities\queries\FieldQuery;
use skewer\components\sluggable\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "form".
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $handler_type
 * @property string $handler_value
 * @property bool $captcha
 * @property bool $hide_field
 * @property bool $block_js
 * @property int $answer
 * @property int $agree
 * @property int $crm
 * @property string $template
 * @property string $target_yandex
 * @property string $target_google
 * @property int $show_required_fields
 * @property int $show_header
 * @property int $system
 * @property int $email_in_reply
 * @property string $button
 * @property int $no_send_data_in_letter
 * @property int $type_result_page
 * @property string $class
 * @property int $show_check_back
 * @property string $last_modified_date
 * @property FieldEntity[] $fields
 * @property CrmLinkFormEntity[] $crmLinks
 */
class FormEntity extends PrototypeEntity
{
    public function behaviors()
    {
        return [
            [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'slug',
                'attribute' => 'title',
                'ensureUnique' => true,
                'forceUpdate' => true,
                'maxLengthSlug' => 60,
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
                'updatedAtAttribute' => 'last_modified_date',
            ],
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function afterSave($insert, $changedAttributes)
    {
        self::updateEntity($this->id);

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%form}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['slug', 'title', 'handler_type'], 'required'],
            [['type_result_page'], 'integer'],
            [
                [
                    'captcha',
                    'hide_field',
                    'block_js',
                    'answer',
                    'agree',
                    'crm',
                    'show_required_fields',
                    'show_header',
                    'system',
                    'email_in_reply',
                    'no_send_data_in_letter',
                    'show_check_back',
                ],
                'boolean',
            ],
            [['last_modified_date'], 'safe'],
            [
                [
                    'title',
                    'handler_type',
                    'handler_value',
                    'target_yandex',
                    'template',
                    'target_google',
                    'button',
                    'class',
                ],
                'string',
                'max' => 255,
            ],
            [['slug'], 'string', 'max' => 60]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slug' => 'Slug',
            'title' => 'Title',
            'handler_type' => 'Handler Type',
            'handler_value' => 'Handler Value',
            'captcha' => 'Captcha',
            'hide_field' => 'Hide Field',
            'block_js' => 'Block Js',
            'answer' => 'Answer',
            'agree' => 'Agree',
            'target_yandex' => 'Target Yandex',
            'crm' => 'Crm',
            'last_modified_date' => 'Last Modified Date',
            'template' => 'Template',
            'target_google' => 'Target Google',
            'show_required_fields' => 'Show Required Fields',
            'show_header' => 'Show Header',
            'system' => 'System',
            'email_in_reply' => 'Email In Reply',
            'button' => 'Button',
            'no_send_data_in_letter' => 'No Send Data In Letter',
            'type_result_page' => 'Type Result Page',
            'class' => 'Class',
            'show_check_back' => 'Show Check Back',
        ];
    }

    public function getFields()
    {
        return $this->hasMany(FieldEntity::class, ['form_id' => 'id']);
    }

    public function getCrmLinks()
    {
        return $this->hasMany(CrmLinkFormEntity::class, ['form_id' => 'id']);
    }

    /**
     * @param int $formEntityId
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function updateEntity(int $formEntityId)
    {
        $creator = new FormOrderEntity($formEntityId);
        $creator->updateEntity();
    }

    public static function hasFormWithSlug($slug): bool
    {
        $form = self::findOne(['slug' => $slug]);

        return $form instanceof self;
    }

    public static function hasFormById(int $id): bool
    {
        $form = self::findOne(['id' => $id]);

        return $form instanceof self;
    }

    /**
     * @return array|self[]
     */
    public static function getNotSystemForms()
    {
        return self::find()
            ->where(['system' => false])
            ->all();
    }

    public static function getBySlug(string $slug)
    {
        return FormEntity::find()
            ->where(['slug' => $slug])
            ->one();
    }

    public static function find(): FieldQuery
    {
        return new FieldQuery(static::class);
    }
}
