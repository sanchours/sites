<?php

namespace skewer\components\targets\models;

use skewer\components\ActiveRecord\ActiveRecord;
use Yii;

/**
 * This is the model class for table "target_selectors".
 *
 * @property int $id
 * @property string $selector
 * @property string $name
 * @property string $type
 * @property string $title
 */
class TargetSelectors extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'target_selectors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'selector'], 'required'],
            [['name', 'type', 'title'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ReachGoal', 'field_id'),
            'selector' => Yii::t('ReachGoal', 'field_selector'),
            'name' => Yii::t('ReachGoal', 'field_yandex_target'),
            'type' => Yii::t('ReachGoal', 'field_google_target'),
            'title' => Yii::t('ReachGoal', 'field_title_selector'),
        ];
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new TargetSelectors();

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }
}
