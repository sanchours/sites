<?php

namespace skewer\components\design\model;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "css_data_groups".
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property int $parent
 * @property string $layer
 * @property int $visible
 * @property int $priority
 */
class Groups extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'css_data_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'title', 'parent', 'priority'], 'required'],
            [['parent', 'visible', 'priority'], 'integer'],
            [['name'], 'string', 'max' => 128],
            [['title'], 'string', 'max' => 255],
            [['layer'], 'string', 'max' => 20],
            [['parent', 'name', 'layer'], 'unique', 'targetAttribute' => ['parent', 'name', 'layer'], 'message' => 'The combination of Name, Parent and Layer has already been taken.'],
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
            'parent' => 'Parent',
            'layer' => 'Layer',
            'visible' => 'Visible',
            'priority' => 'Priority',
        ];
    }
}
