<?php

namespace skewer\build\Tool\Logger;

use skewer\base\log\models\Log;
use skewer\components\auth\models\Users;
use yii\helpers\ArrayHelper;

class Api
{
    private static $aEventLevelList = [
        1 => 'level_critical',
        2 => 'level_warning',
        3 => 'level_error',
        4 => 'level_notice',
    ];

    private static $aLogType = [
        71 => 'type_user',
        72 => 'type_cron',
        73 => 'type_system',
        74 => 'type_debug',
    ];

    /* State List */

    /**
     * @static
     *
     * @return array
     */
    public static function getListFields()
    {
        return [
            'id',
            'login',
            'event_time',
            'title',
            'module_title',
            'event_title',
            'log_title',
            'ip',
        ];
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getDetailFields()
    {
        return [
            'id',
            'event_time',
            'event_title',
            'log_title',
            'title',
            'module_title',
            'initiator',
            'ip',
            'proxy_ip',
            'external_id',
            'description',
        ];
    }

    /**
     * @static
     *
     * @param $aInputData
     *
     * @return array
     */
    public static function getListItems($aInputData)
    {
        $aItems = Log::getItemsList($aInputData);

        $aModules = self::getModules();

        if (count($aItems['items'])) {
            foreach ($aItems['items'] as &$aItem) {
                $aItem['title'] = \Yii::t('logger', $aItem['title']);
                $aItem['event_title'] = self::getLevelTitle($aItem['event_type']);
                $aItem['log_title'] = \Yii::t('logger', self::$aLogType[$aItem['log_type']]);
                $aItem['module_title'] = (isset($aModules[$aItem['module']])) ? $aModules[$aItem['module']] : '';
                $oDate = date_create_from_format('Y-m-d H:i:s', $aItem['event_time']);
                $aItem['event_time'] = $oDate->format('d.m.Y H:i');
                if (!$aItem['login']) {
                    $aItem['login'] = ' - system - ';
                }

                if (mb_strpos($aItem['initiator'], 'canape-id') !== false) {
                    $aItem['login'] = $aItem['initiator'];
                }
            }
        }

        return $aItems;
    }

    /**
     * @static
     *
     * @return array
     * не учитывется слой при работе модуля, что не правильно
     *      должно быть типа Page\Title, а не просто Title
     *      но для этого придется привести текущие значения в базе,
     *      что можно сделать на основе текущего кода метода сделав массив соответствия
     *      типа [Title => Page\Title]
     */
    public static function getModules()
    {
        $aModules = [];

        foreach (\Yii::$app->register->getLayerList() as $sLayer) {
            foreach (\Yii::$app->register->getModuleList($sLayer) as $sModule) {
                $oModuleConfig = \Yii::$app->register->getModuleConfig($sModule, $sLayer);
                $aModules[$sModule] = sprintf(
                    '%s (%s)',
                    $oModuleConfig->getTitle(),
                    $oModuleConfig->getLayer()
                );
            }
        }

        $aTables = \Yii::$app->db->createCommand('Show tables')->queryAll(\PDO::FETCH_NUM);

        foreach (ArrayHelper::getColumn($aTables, '0') as $item) {
            $item = 'DB (' . $item . ')';
            $aModules[$item] = $item;
        }

        asort($aModules);

        return $aModules;
    }

    /**
     * @static
     */
    public static function getUsersLogin()
    {
        $aUsers = ['0' => ' - system - '];

        $aData = Users::find()->select(['login'])->asArray()->all();

        if ($aData) {
            foreach ($aData as $aItem) {
                $aUsers[$aItem['login']] = $aItem['login'];
            }
        }

        /*Выберем из лога уникальных пользователей пришедших с canape-id*/
        $aCanapeIdUsers = Log::findBySql("SELECT `initiator` FROM `log` WHERE `initiator` LIKE('%canape-id%') GROUP BY `initiator`")
            ->asArray()
            ->all();

        foreach (ArrayHelper::getColumn($aCanapeIdUsers, 'initiator') as $item) {
            $aUsers[$item] = $item;
        }

        return $aUsers;
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getEventLevels()
    {
        $aOut = [];
        foreach (self::$aEventLevelList as $iId => $sName) {
            $aOut[$iId] = self::getLevelTitle($iId);
        }

        return $aOut;
    }

    /**
     * Отдает назвыание уровня записл логов по id.
     *
     * @param int $iLevel
     *
     * @return string
     */
    public static function getLevelTitle($iLevel)
    {
        if (isset(self::$aEventLevelList[$iLevel])) {
            return \Yii::t('logger', self::$aEventLevelList[$iLevel]);
        }

        return 'unknown';
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getLogTypes()
    {
        $aOut = [];
        foreach (self::$aLogType as $iId => $sName) {
            $aOut[$iId] = self::getTypeTitle($iId);
        }

        return $aOut;
    }

    /**
     * Отдает назвыание уровня записл логов по id.
     *
     * @param int $iLevel
     *
     * @return string
     */
    public static function getTypeTitle($iLevel)
    {
        if (isset(self::$aLogType[$iLevel])) {
            return \Yii::t('logger', self::$aLogType[$iLevel]);
        }

        return 'unknown';
    }

    public static function clearLog()
    {
        return Log::deleteAll();
    }
}//class
