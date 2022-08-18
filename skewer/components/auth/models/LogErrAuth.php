<?php

namespace skewer\components\auth\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "log_err_auth".
 *
 * @property int $id
 * @property string $login
 * @property string $event_time
 * @property string $ip
 */
class LogErrAuth extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_err_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login', 'ip'], 'required'],
            [['event_time'], 'safe'],
            [['login'], 'string', 'max' => 255],
            [['ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'event_time' => 'Event Time',
            'ip' => 'Ip',
        ];
    }

    public static function getEntry($sLogin)
    {
        //время блокирования
        $sTimeReset = (timeExcess) ?: 5;
        $sTime = date('Y-m-d H:i:s', strtotime("-{$sTimeReset} minutes"));
        $iLodUser = LogErrAuth::find()->where('event_time > :time', [':time' => $sTime])->andWhere(['login' => $sLogin])->count();

        //количество месяцов для очистки логов
        $sTimeReset = (logInputsClear) ?: 1;
        $sTime = date('Y-m-d H:i:s', strtotime("-{$sTimeReset} month"));
        LogErrAuth::deleteAll('event_time < :time', [':time' => $sTime]);

        $iNumbInputs = (numberInputs) ?: 10;
        if ($iLodUser >= $iNumbInputs) {
            return \Yii::t('auth', 'timeExcess');
        }

        return '';
    }

    /**
     * Метод добавления записи об ошибки входа.
     *
     * @param $sLogin
     *
     * @return  bool|int
     */
    public static function addToLogErr($sLogin)
    {
        $log = new LogErrAuth();
        $log->login = $sLogin;
        $log->ip = \Yii::$app->request->getUserIP();

        return $log->save() ? $log->id : false;
    }
}
