<?php

namespace skewer\components\auth\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "group_policy_func".
 *
 * @property int $policy_id
 * @property string $module_name
 * @property string $param_name
 * @property string $value
 */
class GroupPolicyFunc extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_policy_func';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['policy_id', 'module_name', 'param_name', 'value'], 'required'],
            [['policy_id'], 'integer'],
            [['module_name'], 'string', 'max' => 255],
            [['param_name'], 'string', 'max' => 40],
            [['value'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'policy_id' => 'Policy ID',
            'module_name' => 'Module Name',
            'param_name' => 'Param Name',
            'value' => 'Value',
        ];
    }
}
