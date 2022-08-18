<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:50.
 */

namespace skewer\components\tokensAuth;

use skewer\components\auth\Auth;

class Session
{
    private static $sMode = null;

    public static function checkSession()
    {
        if (isset($_SESSION['auth']['admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Установка сессии для пользователя под таким логином
     *
     * @param string $sMode
     *
     * @return string
     */
    public static function setSession($sMode = 'sys')
    {
        self::$sMode = $sMode;

        $aUser = \skewer\components\auth\models\Users::find()
            ->where(['login' => $sMode])
            ->asArray()
            ->one();

        Auth::loadUser('admin', $aUser['id']);

        // обновляем дату последнего захода
        \skewer\components\auth\Users::updateLoginTime($aUser['id']);

        return session_id();
    }

    /**
     * Сброс сессии по ключу.
     *
     * @param $sSessionId
     */
    public static function unsetSession($sSessionId)
    {
        session_start();
        session_id($sSessionId);
        session_start();
        session_destroy();
        session_commit();
    }
}
