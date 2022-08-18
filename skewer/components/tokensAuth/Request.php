<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:48.
 */

namespace skewer\components\tokensAuth;

class Request
{
    /**
     * Массив данных пришедших в запросе.
     *
     * @var array
     */
    private static $aRequest = [];

    /**
     * Собирает пришедшие данные.
     */
    public static function setRequest()
    {
        $aGetData = \Yii::$app->request->get();
        if (!empty($aGetData)) {
            self::$aRequest = $aGetData;
        }

        $aPostData = \Yii::$app->request->post();
        if (!empty($aPostData)) {
            self::$aRequest = $aPostData;
        }

        if (isset(self::$aRequest['data'])) {
            self::$aRequest = json_decode(self::$aRequest['data'], true);
        }
    }

    /**
     * Отдает данные пришедшие в запросе.
     *
     * @return array
     */
    public static function getRequest()
    {
        return self::$aRequest;
    }

    /**
     * Отдает данные по ключу.
     *
     * @param $sName
     * @param $sDefault
     *
     * @return mixed
     */
    public static function getValByKey($sName, $sDefault)
    {
        if (isset(self::$aRequest[$sName])) {
            return self::$aRequest[$sName];
        }

        return $sDefault;
    }
}
