<?php

namespace skewer\base\log\models;

use skewer\app\ConsoleApp;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\models\Users;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * This is the model class for table "log".
 *
 * @property int $id
 * @property string $event_time
 * @property int $event_type
 * @property int $log_type
 * @property string $title
 * @property string $module
 * @property string $initiator
 * @property string $ip
 * @property string $proxy_ip
 * @property string $external_id
 * @property string $description
 *
 * @method static Log findOne($condition)
 */
class Log extends ActiveRecord
{
    /** Тип журнала событий - журнал действий пользователей */
    const logUsers = 71;

    /** Тип журнала событий - журнал планировщика заданий */
    const logCron = 72;

    /** Тип журнала событий - системный журнал */
    const logSystem = 73;

    /** Тип журнала событий - журнал отладки */
    const logDebug = 74;

    protected static $bLogUpdate = false;

    /**
     * Флаг логированть ли создание записи.
     *
     * @var bool
     */
    protected static $bLogCreate = false;

    /**
     * Флаг логировать ли удаление записи.
     *
     * @var bool
     */
    protected static $bLogDelete = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log';
    }

    public static function deleteExpectLimit($iLimit)
    {
        $iAllCount = self::find()->count();
        $iDeleteCount = $iAllCount - $iLimit;

        if ((int) $iDeleteCount > 0) {
            return \skewer\base\orm\Query::DeleteFrom(self::tableName())
                ->order('id')
                ->limit($iDeleteCount)
                ->get();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_time'], 'safe'],
            [['event_type', 'title', 'ip'], 'required'],
            [['event_type', 'log_type'], 'integer'],
            [['description'], 'string'],
            [['title', 'module', 'initiator'], 'string', 'max' => 255],
            [['ip', 'proxy_ip'], 'string', 'max' => 255],
            [['external_id'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'field_id',
            'event_time' => 'field_event_time',
            'event_type' => 'field_event_type',
            'log_type' => 'field_log_type',
            'title' => 'field_title',
            'module' => 'field_module',
            'initiator' => 'field_initiator',
            'ip' => 'field_ip',
            'proxy_ip' => 'field_proxy_ip',
            'external_id' => 'field_external_id',
            'description' => 'field_description',
        ];
    }

    public static function getItemsList($aInputData)
    {
        // маска входного фильтра, он же массив данных для подстановки в запрос

        $f = [ // filter mask
            'limit.start' => 0,
            'limit.count' => 100,
            'order.field' => 'id',
            'order.way' => 'DESC',
            'login' => false,
            'module' => null,
            'event_type' => null,
            'log_type' => null,
            'event_time.sign' => null,
            'event_time.value' => null,
        ];

        // покрываем маску фильтра значениями из входного массива

        if (is_array($aInputData)) {
            foreach ($aInputData as $k0 => $val0) {
                if (is_array($val0)) {
                    foreach ($val0 as $k1 => $val1) {
                        $f[$k0 . '.' . $k1] = $val1;
                    }
                } else {
                    $f[$k0] = $val0;
                }
            }
        }

        /*Базовая выборка*/
        $q = (new Query())
            ->select('`log`.*, `log`.initiator as login')
            ->from('log');

        /*Добавляем логин*/
        if ($f['login']) {
            /*Вытаскиваем ID пользователя по его логину*/
            $oUser = Users::find()
                ->where(['login' => $f['login']])
                ->one();

            if ($oUser !== null) {
                /*Ищем по пользователю который существует*/
                $q->andWhere(['initiator' => $oUser['login']]);
            } else {
                //Определение по пользователю CanapeId
                if (isset($aInputData['login']) && mb_strpos($aInputData['login'], 'canape-id') !== false) {
                    $q->andWhere(['initiator' => $aInputData['login']]);
                } else {
                    /*Этого пользователя не существует, но выведем записи которые ему соответствуют*/
                    $q->andWhere(['initiator' => $oUser['login']]);
                }
            }
        }

        /*Добавляем название модуля*/
        if ($f['module']) {
            $q->andWhere(['module' => $f['module']]);
        }

        /*Добавляем уровень события*/
        if ($f['event_type']) {
            $q->andWhere(['event_type' => $f['event_type']]);
        }

        /*Добавляем тип журнала*/
        if ($f['log_type']) {
            $q->andWhere(['log_type' => $f['log_type']]);
        }

        /*Добавляем временные рамки*/
        if ($f['event_time.sign'] and $f['event_time.value']) {
            if (($f['event_time.sign'] == 'BETWEEN') and is_array($f['event_time.value'])) {
                $timeQ = [
                    $f['event_time.sign'],
                       'event_time',
                    $f['event_time.value'][0],
                    $f['event_time.value'][1],
                ];
            } else {
                $timeQ = [
                    $f['event_time.sign'],
                       'event_time',
                    $f['event_time.value'],
                ];
            }

            $q->andWhere($timeQ);
        }

        // если не сисадмин - вырезаем системные записи из выборки
        if (!CurrentAdmin::isSystemMode()) {
            $q->andWhere('log_type != ' . self::logSystem);
            $q->andWhere('Initiator NOT LIKE ' . '"%sys%"');
        }

        /*Дополняем сортировкой*/
        $q->orderBy($f['order.field'] . ' ' . $f['order.way']);

        // Выполняем запрос и собираем выходной массив

        $provider = new ActiveDataProvider([
            'query' => $q,
            'pagination' => [
                'page' => floor($f['limit.start'] / $f['limit.count']),
                'pageSize' => $f['limit.count'],
            ],
        ]);

        return [
            'items' => $provider->getModels(),
            'count' => $provider->getTotalCount(),
        ];
    }

    /**
     * @static Метод добавления отчета о критическом действии
     *
     * @param string $sTitle Наименование события
     * @param string $sDescription Полное описание события
     * @param int $iLogType Тип журнала событий
     * @param string $sCalledClass Класс, вызвавший исключение
     * @param string  $sTitleCalled Название модуля, инициировавшего добавление записи в лог
     *
     * @return bool|int
     */
    public static function addCriticalReport($sTitle, $sDescription, $iLogType, $sCalledClass, $sTitleCalled = '')
    {
        if ($sTitleCalled) {
            $sTotalTitle = implode(':', [$sCalledClass, $sTitleCalled]);
        } else {
            $sTotalTitle = $sCalledClass;
        }

        return self::addToLog($sTitle, $sDescription, $sTotalTitle, 1, $iLogType);
    }

    /**
     * @static Метод добавления предупреждения
     *
     * @param string $sTitle Наименование события
     * @param string $sDescription Полное описание события
     * @param int $iLogType Тип журнала событий
     * @param string $sCalledClass Класс, вызвавший исключение
     * @param string $sTitleCalled Название модуля, инициировавшего добавление записи в лог
     *
     * @return bool|int
     */
    public static function addWarningReport($sTitle, $sDescription, $iLogType, $sCalledClass, $sTitleCalled = '')
    {
        if ($sTitleCalled) {
            $sTotalTitle = implode(':', [$sCalledClass, $sTitleCalled]);
        } else {
            $sTotalTitle = $sCalledClass;
        }

        return self::addToLog($sTitle, $sDescription, $sTotalTitle, 2, $iLogType);
    }

    /**
     * @static Метод добавления отчета об ошибке
     *
     * @param string $sTitle Наименование события
     * @param string $sDescription Полное описание события
     * @param int $iLogType Тип журнала событий
     * @param string $sCalledClass Класс, вызвавший исключение
     * @param string $sTitleCalled Название модуля, инициировавшего добавление записи в лог
     *
     * @return bool|int
     */
    public static function addErrorReport($sTitle, $sDescription, $iLogType, $sCalledClass, $sTitleCalled = '')
    {
        if ($sTitleCalled) {
            $sTotalTitle = implode(':', [$sCalledClass, $sTitleCalled]);
        } else {
            $sTotalTitle = $sCalledClass;
        }

        return self::addToLog($sTitle, $sDescription, $sTotalTitle, 3, $iLogType);
    }

    /**
     * Метод добавления уведомления.
     *
     * @static
     *
     * @param string $sTitle Наименование события
     * @param array|string $mDescription Полное описание события
     * @param int $iLogType Тип журнала событий
     * @param string $sCalledClass Класс, вызвавший добавление
     * @param string $sTitleCalled Название модуля, инициировавшего добавление записи в лог
     *
     * @return bool|int
     */
    public static function addNoticeReport($sTitle, $mDescription, $iLogType, $sCalledClass, $sTitleCalled = '')
    {
        if ($sTitleCalled) {
            $sTotalTitle = implode(':', [$sCalledClass, $sTitleCalled]);
        } else {
            $sTotalTitle = $sCalledClass;
        }

        // приведение к нужному виду
        $mDescription = self::buildDescription($mDescription);

        return self::addToLog($sTitle, $mDescription, $sTotalTitle, 4, $iLogType);
    }

    /**
     * @static Метод добавления записи определенного типа в лог
     *
     * @param $sTitle
     * @param $sDescription
     * @param $sModule
     * @param $iEventType
     * @param $iLogType
     *
     * @return bool|int
     */
    public static function addToLog($sTitle, $sDescription, $sModule, $iEventType, $iLogType)
    {
        if (!\Yii::$app->getParam(['log', 'enable'])) {
            return true;
        }

        switch ($iLogType) {
            case self::logUsers:

                if (!\Yii::$app->getParam(['log', 'users'])) {
                    return true;
                }
                break;

            case self::logSystem:

                if (!\Yii::$app->getParam(['log', 'system'])) {
                    return true;
                }
                break;

            case self::logCron:

                if (!\Yii::$app->getParam(['log', 'cron'])) {
                    return true;
                }
                break;

            case self::logDebug:

                if (!\Yii::$app->getParam(['log', 'debug'])) {
                    return true;
                }
                break;
        }

        $log = new Log();
        $log->event_type = $iEventType;
        $log->log_type = $iLogType;
        $log->title = $sTitle;
        $log->module = $sModule;
        $log->initiator = self::getUserName();
        $log->ip = self::getUserIp();
        $log->proxy_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
        $log->external_id = '';
        $log->description = $sDescription;

        return $log->save() ? $log->id : false;
    }

    /**
     * Отдает имя пользователя для записи в лог.
     *
     * @return bool|string
     */
    protected static function getUserName()
    {
        if (\Yii::$app instanceof ConsoleApp) {
            return 'console';
        }
        if (\Yii::$app->session->get('current_canape_id_login') !== null) {
            $aCanapeIdData = \Yii::$app->session->get('current_canape_id_login');
            $sUsername = $aCanapeIdData['username'] . ' [' . $aCanapeIdData['auth_mode'] . '/canape-id]';
        } else {
            $sUsername = CurrentAdmin::getLogin();
        }

        return $sUsername;
    }

    /**
     * @return null|string
     */
    protected static function getUserIp()
    {
        if (\Yii::$app instanceof ConsoleApp) {
            return '';
        }

        return \Yii::$app->request->getUserIP();
    }

    /**
     * Форматирование данных в строку.
     *
     * @static
     *
     * @param mixed $mDescArray
     *
     * @return string
     */
    public static function buildDescription($mDescArray)
    {
        // приведение к типу описания
        if (is_array($mDescArray)) {
            $mDescription = json_encode($mDescArray);
        } else {
            $mDescription = (string) $mDescArray;
        }

        return $mDescription;
    }

    /**
     * Включает логи на площадке.
     */
    public static function enableLogs()
    {
        ActiveRecord::enableLogs();
        \skewer\base\orm\ActiveRecord::enableLogs();
    }

    /**
     * Отключает логи на площадке.
     */
    public static function disableLogs()
    {
        ActiveRecord::disableLogs();
        \skewer\base\orm\ActiveRecord::disableLogs();
    }
}
