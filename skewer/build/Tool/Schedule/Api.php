<?php

namespace skewer\build\Tool\Schedule;

use skewer\base\ui\builder\FormBuilder;

class Api
{
    /*Статусы задач в планировщике*/
    const iStatusActive = 1;
    const iStatusStopped = 2;
    const iStatusPaused = 3;

    public static function getPriorityArray()
    {
        return [
            1 => \Yii::t('schedule', 'priority_low'),
            2 => \Yii::t('schedule', 'priority_normal'),
            3 => \Yii::t('schedule', 'priority_high'),
            4 => \Yii::t('schedule', 'priority_critical'),
        ];
    }

    public static function getResourceArray()
    {
        return [
            3 => \Yii::t('schedule', 'resource_background'),
            4 => \Yii::t('schedule', 'resource_normal'),
            7 => \Yii::t('schedule', 'resource_intensive'),
            9 => \Yii::t('schedule', 'resource_critical'),
        ];
    }

    public static function getTargetArray()
    {
        return [
            1 => \Yii::t('schedule', 'target_site'),
            2 => \Yii::t('schedule', 'target_server'),
            3 => \Yii::t('schedule', 'target_system'),
        ];
    }

    public static function getStatusArray()
    {
        return [
            self::iStatusActive => \Yii::t('schedule', 'status_active'),
            self::iStatusStopped => \Yii::t('schedule', 'status_stopped'),
            self::iStatusPaused => \Yii::t('schedule', 'status_paused'),
        ];
    }

    /**
     * Добавляет в интерфейс настройки времени выполнения задачи.
     *
     * @param FormBuilder $oForm
     *
     * @return FormBuilder
     */
    public static function addRunTimeSettings(FormBuilder $oForm)
    {
        $oForm
            ->fieldSelect('status', \Yii::t('schedule', 'status'), self::getStatusArray(), [], false)
            ->field('c_min', \Yii::t('schedule', 'c_min'), 'string')
            ->field('c_hour', \Yii::t('schedule', 'c_hour'), 'string')
            ->field('c_day', \Yii::t('schedule', 'c_day'))
            ->field('c_month', \Yii::t('schedule', 'c_month'))
            ->field('c_dow', \Yii::t('schedule', 'c_dow'), 'string', [
                'subtext' => \Yii::t('data/schedule', 'desc_time_settings'),
            ]);

        return $oForm;
    }

    /**
     * Дефолтные настройки времени выполнения задачи.
     *
     * @return array
     */
    public static function getBlankSettingTime()
    {
        return [
            'c_min' => '*',
            'c_hour' => '*',
            'c_day' => '*',
            'c_month' => '*',
            'c_dow' => '*',
            'status' => self::iStatusStopped,
        ];
    }
}
