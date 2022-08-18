<?php

namespace skewer\components\auth\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "group_policy_module".
 *
 * @property int $policy_id
 * @property string $module_name
 * @property string $title
 */
class GroupPolicyModule extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_policy_module';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['policy_id', 'module_name', 'title'], 'required'],
            [['policy_id'], 'integer'],
            [['module_name', 'title'], 'string', 'max' => 255],
            [['policy_id', 'module_name'], 'unique', 'targetAttribute' => ['policy_id', 'module_name'], 'message' => 'The combination of Policy ID and Module Name has already been taken.'],
            [['policy_id', 'module_name'], 'unique', 'targetAttribute' => ['policy_id', 'module_name'], 'message' => 'The combination of Policy ID and Module Name has already been taken.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'policy_id' => \Yii::t('policy', 'policy_id'),
            'module_name' => \Yii::t('policy', 'module_name'),
            'title' => \Yii::t('policy', 'title'),
        ];
    }
}
