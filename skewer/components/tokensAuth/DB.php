<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:51.
 */

namespace skewer\components\tokensAuth;

use skewer\base\SysVar;
use skewer\components\auth\models\Users;

class DB
{
    /**
     * Получение данных о пользователе сайта по логину.
     *
     * @param $sUserName
     *
     * @return array
     */
    public static function getUserData($sUserName)
    {
        $aData = Users::findOne(['login' => $sUserName])->getAttributes();

        return $aData;
    }

    /**
     * Получение ключа приложения.
     *
     * @return string
     */
    public static function getAppKey()
    {
        return SysVar::get('application_key', 'no_key');
    }

    /**
     * Установка ключа приложения.
     *
     * @param $sKey
     *
     * @return mixed
     */
    public static function setAppKey($sKey)
    {
        return SysVar::set('application_key', $sKey);
    }

    /**
     * Блокировка SYS для логина.
     */
    public static function removeSys()
    {
        Users::updateAll(['pass' => '', 'global_id' => 0], ['login' => 'sys']);
    }

    /**
     * Получение ключа приложения.
     *
     * @return string
     */
    public static function getPublicKey()
    {
        return SysVar::get('application_public_key', 'no_key');
    }

    /**
     * Установка ключа приложения.
     *
     * @param $sKey
     *
     * @return mixed
     */
    public static function setPublicKey($sKey)
    {
        return SysVar::set('application_public_key', $sKey);
    }
}
