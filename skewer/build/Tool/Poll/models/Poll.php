<?php

namespace skewer\build\Tool\Poll\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "polls".
 *
 * @property int $id
 * @property string $title
 * @property string $question
 * @property string $start_date
 * @property string $stop_date
 * @property string $location
 * @property int $active
 * @property int $on_main
 * @property int $on_allpages
 * @property int $on_include
 * @property int $section
 * @property int $sort
 * @property string $last_modified_date
 */
class Poll extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'polls';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['location', 'sort'], 'required'],
            [['start_date', 'stop_date', 'last_modified_date'], 'safe'],
            [['active', 'on_main', 'on_allpages', 'on_include', 'section', 'sort'], 'integer'],
            [['title', 'question', 'location'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'question' => 'Question',
            'start_date' => 'Start Date',
            'stop_date' => 'Stop Date',
            'location' => 'Location',
            'active' => 'Active',
            'on_main' => 'On Main',
            'on_allpages' => 'On Allpages',
            'on_include' => 'On Include',
            'section' => 'Section',
            'sort' => 'Sort',
            'last_modified_date' => 'Last modified date',
        ];
    }

    public function getAnswers()
    {
        return $this
            ->hasMany(PollAnswer::className(), ['parent_poll' => 'id'])
            ->orderBy(['sort' => SORT_ASC]);
    }

    public function beforeSave($insert)
    {
        $this->last_modified_date = date('Y-m-d H:i:s', time());

        return parent::beforeSave($insert);
    }

    public function beforeDelete() // удаляем связанные варианты ответов
    {
        if (parent::beforeDelete()) {
            PollAnswer::deleteAll('parent_poll=:id', ['id' => $this->id]);

            return true;
        }

        return false;
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return (new \yii\db\Query())->select('MAX(`last_modified_date`) as max')->from(self::tableName())->one();
    }
}
