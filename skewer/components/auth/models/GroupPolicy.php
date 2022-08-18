<?php

namespace skewer\components\auth\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "group_policy".
 *
 * @property int $id
 * @property string $alias
 * @property string $title
 * @property string $area
 * @property int $access_level
 * @property int $active
 * @property int $del_block
 */
class GroupPolicy extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_policy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'access_level', 'active'], 'required'],
            [['access_level', 'active', 'del_block'], 'integer'],
            [['alias', 'area'], 'string', 'max' => 20],
            [['title'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alias' => \Yii::t('auth', 'alias'),
            'title' => \Yii::t('auth', 'policytitle'),
            'area' => \Yii::t('auth', 'area'),
            'access_level' => \Yii::t('auth', 'access_level'),
            'active' => \Yii::t('auth', 'active'),
            'del_block' => \Yii::t('auth', 'del_block'),
        ];
    }
}
