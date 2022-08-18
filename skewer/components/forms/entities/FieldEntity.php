<?php

declare(strict_types=1);

namespace skewer\components\forms\entities;

use skewer\components\sluggable\SluggableBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "form_field".
 *
 * @property int $id
 * @property int $form_id
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $type
 * @property int $required
 * @property string $default
 * @property int $max_length
 * @property string $type_of_valid
 * @property string $spec_style
 * @property int $priority
 * @property string $label_position
 * @property int $new_line
 * @property int $width_factor
 * @property int $display_type
 * @property int $group_prev_field
 * @property string $class_modify
 */
class FieldEntity extends PrototypeEntity
{
    public function behaviors()
    {
        return [
            [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'slug',
                'attribute' => 'title',
                'maxLengthSlug' => 64,
                'ensureUnique' => true,
                'forceUpdate' => false,
                'uniqueValidator' => [
                    'filter' => function (ActiveQuery $query) {
                        $query
                            ->andWhere([
                                'form_id' => $this->form_id,
                            ]);

                        return $query;
                    },
                ],
            ],
        ];
    }

    public static function tableName()
    {
        return '{{%form_field}}';
    }

    public function rules()
    {
        return [
            [
                [
                    'title',
                    'slug',
                    'description',
                    'type',
                    'required',
                    'default',
                    'max_length',
                    'type_of_valid',
                    'spec_style',
                    'priority',
                    'label_position',
                    'new_line',
                    'width_factor',
                    'display_type',
                    'group_prev_field',
                    'class_modify',
                ],
                'safe',
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if (!$this->priority) {
            $maxPosition = self::find()
                ->where(['form_id' => (int) $this->form_id])
                ->max('priority');

            $this->priority = $maxPosition + 1;
        }

        return parent::beforeSave($insert);
    }

    public static function getFieldsByIdForm(int $id): array
    {
        return self::find()
            ->where(['form_id' => $id])
            ->orderBy('priority')
            ->all();
    }

    public static function getFieldsByFormIdAndType(
        int $idForm,
        string $typeField
    ): array {
        return self::find()
            ->where([
                'form_id' => $idForm,
                'type' => $typeField,
            ])
            ->asArray()
            ->all();
    }
}
