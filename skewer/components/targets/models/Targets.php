<?php

namespace skewer\components\targets\models;

use skewer\components\ActiveRecord\ActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "reach_goal_target".
 *
 * @property int $id
 * @property string $name
 * @property int $title
 * @property string $category
 * @property string $type
 * @property string $annonymous
 */
class Targets extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reach_goal_target';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'title'], 'required'],
            [['category', 'type'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ReachGoal', 'field_id'),
            'name' => Yii::t('ReachGoal', 'field_name'),
            'title' => Yii::t('ReachGoal', 'field_title'),
            'category' => Yii::t('ReachGoal', 'field_category'),
            'type' => Yii::t('ReachGoal', 'field_type'),
        ];
    }

    public static function getNewRow($aData = [], $sType = '')
    {
        $oRow = new Targets();

        $oRow->type = $sType;

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    public static function getByTypeArray($sType)
    {
        $aTargets = Targets::find()
            ->where(['type' => $sType])
            ->asArray()
            ->all();

        return ArrayHelper::map($aTargets, 'name', 'title');
    }

    /**
     * Это цель яндекса?
     *
     * @return bool
     */
    public function isYandex()
    {
        return $this->type == 'yandex';
    }

    /**
     * Это цель Google?
     *
     * @return bool
     */
    public function isGoogle()
    {
        return $this->type == 'google';
    }
}
