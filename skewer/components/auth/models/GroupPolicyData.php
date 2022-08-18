<?php

namespace skewer\components\auth\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "group_policy_data".
 *
 * @property int $policy_id
 * @property int $start_section
 * @property string $read_disable
 * @property string $read_enable
 * @property string $cache_read
 * @property int $version
 */
class GroupPolicyData extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_policy_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['policy_id'], 'required'],
            [['policy_id', 'start_section'], 'integer'],
            [['read_disable', 'read_enable', 'cache_read'], 'string'],
            [['version'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'policy_id' => 'Policy ID',
            'start_section' => 'Start Section',
            'read_disable' => 'Read Disable',
            'read_enable' => 'Read Enable',
            'cache_read' => 'Cache Read',
            'version' => 'Version',
        ];
    }
}
