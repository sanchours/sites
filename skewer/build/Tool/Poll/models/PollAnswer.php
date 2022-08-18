<?php

namespace skewer\build\Tool\Poll\models;

use skewer\components\ActiveRecord\ActiveRecord;
use Yii;

/**
 * This is the model class for table "polls_answers".
 *
 * @property int $answer_id
 * @property string $title
 * @property int $parent_poll
 * @property int $value
 * @property int $sort
 */
class PollAnswer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'polls_answers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'parent_poll', 'value', 'sort'], 'required'],
            [['parent_poll', 'value', 'sort'], 'integer'],
            [['title'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'answer_id' => Yii::t('poll', 'answer_id'),
            'title' => Yii::t('poll', 'answer_title'),
            'parent_poll' => Yii::t('poll', 'answer_parent_poll'),
            'value' => Yii::t('poll', 'answer_value'),
            'sort' => Yii::t('poll', 'sort'),
        ];
    }

    public function getPool()
    {
        return $this->hasOne(Poll::className(), ['id' => 'parent_poll']);
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->sort = (int) PollAnswer::find()
                ->where(['parent_poll' => $this->parent_poll])
                ->max('sort') + 1;
        }

        // значение может быть только >=0
        $this->value = abs((int) $this->value);

        return true;
    }

    public function afterDelete()
    {
        /** @var Poll $oPoll */
        $oPoll = $this->getPool()->one();
        $oPoll->last_modified_date = date('Y-m-d H:i:s', time());
        $oPoll->save();

        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        /** @var Poll $oPoll */
        $oPoll = $this->getPool()->one();
        $oPoll->last_modified_date = date('Y-m-d H:i:s', time());
        $oPoll->save();

        parent::afterSave($insert, $changedAttributes);
    }
}
