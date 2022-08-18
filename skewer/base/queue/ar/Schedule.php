<?php

namespace skewer\base\queue\ar;

use skewer\components\ActiveRecord\ActiveRecord;
use yii\base\Exception;

/**
 * This is the model class for table "schedule".
 *
 * @property int $id
 * @property string $title
 * @property string $name
 * @property string $command
 * @property int $priority
 * @property int $resource_use
 * @property int $target_area
 * @property int $status
 * @property int $c_min
 * @property int $c_hour
 * @property int $c_day
 * @property int $c_month
 * @property int $c_dow
 */
class Schedule extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'schedule';
    }

    public function setDefaultValues()
    {
        $this->setAttributes(
            [
                'id' => 0,
                'priority' => 2,
                'resource_use' => 4,
                'target_area' => 1,
                'status' => 1,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'title', 'command', 'c_min', 'c_hour', 'c_day', 'c_month', 'c_dow'], 'string'],
            [['name', 'title', 'command', 'priority', 'resource_use', 'target_area', 'status'], 'required'],
            [['priority', 'resource_use', 'target_area', 'status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('schedule', 'id'),
            'title' => \Yii::t('schedule', 'title'),
            'name' => \Yii::t('schedule', 'name'),
            'command' => \Yii::t('schedule', 'command'),
            'priority' => \Yii::t('schedule', 'priority'),
            'resource_use' => \Yii::t('schedule', 'resource_use'),
            'target_area' => \Yii::t('schedule', 'target_area'),
            'status' => \Yii::t('schedule', 'status'),
            'c_min' => \Yii::t('schedule', 'c_min'),
            'c_hour' => \Yii::t('schedule', 'c_hour'),
            'c_day' => \Yii::t('schedule', 'c_day'),
            'c_month' => \Yii::t('schedule', 'c_month'),
            'c_dow' => \Yii::t('schedule', 'c_dow'),
        ];
    }

    /**
     * получение ид задания в рассписании по имени.
     *
     * @static
     *
     * @param $name
     *
     * @return bool
     */
    public static function getIdByName($name)
    {
        if ($res = self::findOne(['name' => $name])) {
            return $res->id;
        }

        return false;
    }

    public function beforeValidate()
    {
        $this->c_min = self::validateVal($this->c_min, \Yii::t('schedule', 'c_min'), 59);
        $this->c_hour = self::validateVal($this->c_hour, \Yii::t('schedule', 'c_hour'), 23);
        $this->c_dow = self::validateVal($this->c_dow, \Yii::t('schedule', 'c_dow'), 7, true);
        $this->c_day = self::validateVal($this->c_day, \Yii::t('schedule', 'c_day'), 31);
        $this->c_month = self::validateVal($this->c_month, \Yii::t('schedule', 'c_month'), 12);

        return parent::beforeValidate();
    }

    /**
     * Проверяет значение можно ли его использовать для крона.
     *
     * @param $sVal
     * @param $sName
     * @param $sMax
     * @param mixed $lockEvery
     *
     * @throws Exception
     *
     * @return string
     */
    public function validateVal($sVal, $sName, $sMax, $lockEvery = false)
    {
        /*Попытаемся разобрать как "интервал запука"*/
        $matches = [];
        preg_match('/[0-9]{1,2}-[0-9]{1,2}/', $sVal, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches)) {
            return $matches[0][0];
        }

        if (!$lockEvery) {
            /*Попытамся разобрать как "запуск каждые"*/
            $matches = [];
            preg_match('/\\*\\/[0-9]{1,5}/', $sVal, $matches, PREG_OFFSET_CAPTURE);

            if (!empty($matches)) {
                return $matches[0][0];
            }
        }

        if ((is_numeric($sVal)) and ($sVal <= $sMax)) {
            return (string) $sVal;
        }

        if (($sVal == '') or ($sVal == '*')) {
            return '*';
        }

        throw new Exception(\Yii::t('schedule', 'invalid_value', ['name' => $sName]));
    }
}
