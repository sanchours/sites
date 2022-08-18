<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:46.
 */

namespace skewer\components\tokensAuth;

use skewer\base\log\models\Log;
use skewer\components\auth\Users;

class Api
{
    private static $aQueryData;

    public static function checkUpdates()
    {
        self::$aQueryData = [
            'cmd' => 'getVersion',
        ];

        Response::$aData = json_decode(Gateway::getData(UrlHelper::getTokensUrl('get-version'), self::$aQueryData), true);

        if (isset(Response::$aData['content']) && Response::$aData['content'] != Config::$sVersion) {
            echo 'Вы используете устаревшую версию sys.php';
            exit;
        }
    }

    public static function getSysVersion()
    {
        echo Config::$sVersion;
        exit;
    }

    /**
     * Подтягивание формы авторизации и ее отрисовка.
     */
    public static function getToken()
    {
        $sReturnLink = Request::getValByKey('return_link', 'none');
        $_SESSION['need_access_to'] = str_replace('%23', '#', $sReturnLink);

        self::$aQueryData = [
            'cmd' => 'getToken',
            'redirect_link' => 'http://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . $_SERVER['SCRIPT_NAME'],
            'public_key' => DB::getPublicKey(),
            'service_name' => Config::$sServiceName,
            'site_type' => 'Canape3',
        ];

        Response::$aData['mode'] = 'redirect';
        Response::$aData['redirect_link'] = UrlHelper::getTokensUrl('get-token');
        Response::$aData['params'] = self::$aQueryData;
    }

    /**
     * Получение от сервиса токенов уникального ключа, запись в SysVar и редирект на состояние отрисовки формы.
     */
    public static function getKey()
    {
        self::$aQueryData = [
            'cmd' => 'getKey',
            'site_url' => str_replace('www.', '', $_SERVER['HTTP_HOST']),
            'site_type' => 'Canape3',
        ];

        Response::$aData['mode'] = 'redirect';
        Response::$aData['redirect_link'] = UrlHelper::getTokensUrl('get-key');
        Response::$aData['params'] = self::$aQueryData;

        Response::execute();
    }

    /**
     * Проверка токена через сервис токенов.
     */
    public static function checkToken()
    {
        self::$aQueryData['cmd'] = 'checkToken';
        self::$aQueryData['token'] = Request::getValByKey('token', '');
        self::$aQueryData['app_key'] = hash('sha512', DB::getAppKey() . str_replace('www.', '', $_SERVER['HTTP_HOST']));
        self::$aQueryData['user_ip'] = $_SERVER['REMOTE_ADDR'];
        self::$aQueryData['site_name'] = $_SERVER['HTTP_HOST'];

        Response::$aData = json_decode(Gateway::getData(UrlHelper::getTokensUrl('check-token'), self::$aQueryData), true);

        if (Response::$aData['content'] == '1') {
            $iSessionId = Session::setSession(Response::$aData['auth_mode']);

            /*Запросим данные о последнем логине этого пользователя*/
            if (isset(Response::$aData['username'])) {
                $sUserName = Response::$aData['username'];
            }

            $sUserEmail = '';
            if (isset(Response::$aData['email'])) {
                $sUserEmail = Response::$aData['email'];
            }

            if (isset(Response::$aData['auth_mode'])) {
                $sAuthMode = Response::$aData['auth_mode'];
            }

            if (isset($sUserName, $sAuthMode) && $sUserName && $sAuthMode) {
                if ($iSessionId) {
                    Log::addNoticeReport('Вход через CanapeId', json_encode([
                        'username' => $sUserName,
                        'email' => $sUserEmail,
                        'auth_mode' => $sAuthMode,
                    ]), Log::logUsers, 'Auth');
                }

                \Yii::$app->session->set(
                    'current_canape_id_login',
                    [
                            'username' => $sUserName,
                            'email' => $sUserEmail,
                            'auth_mode' => $sAuthMode,
                        ]
                );
            }

            self::updateLoginTime($sAuthMode);

            self::$aQueryData = [
                'cmd' => 'setKill',
                'session_id' => $iSessionId,
                'app_key' => hash('sha512', DB::getAppKey() . str_replace('www.', '', $_SERVER['HTTP_HOST'])),
                'token' => Request::getValByKey('token', ''),
                'target_url' => 'http://' . str_replace('www.', '', $_SERVER['HTTP_HOST']),
            ];

            /*Отдадим сервису токенов ид сессии*/
            Response::$aData = json_decode(Gateway::getData(UrlHelper::getTokensUrl('set-kill'), self::$aQueryData), true);

            Response::$aData = [
                'mode' => 'redirect',
                'redirect_link' => '/admin/',
            ];

            if (isset($_SESSION['need_access_to']) && $_SESSION['need_access_to'] !== 'none') {
                Response::$aData['redirect_link'] = $_SESSION['need_access_to'];
            }
        } else {
            self::getToken();
        }
    }

    public static function setKey()
    {
        if (DB::getAppKey() == 'no_key') {
            $aRequest = Request::getRequest();
            DB::setAppKey($aRequest['app_key']);
            DB::setPublicKey($aRequest['public_key']);
        }

        Api::getToken();
    }

    /**
     * Удаление сессии по ее ID.
     */
    public static function killSession()
    {
        Session::unsetSession(Request::getValByKey('session_id', ''));
        Response::$aData['content'] = 1;
    }

    /**
     * Проверяем наличие пользователя
     * и обновляем дату последнего захода.
     *
     * @param mixed $sLogin
     */
    public static function updateLoginTime($sLogin)
    {
        if (is_string($sLogin) && $id = Users::getIdByLogin($sLogin)) {
            if ($id != 0) {
                $param = ['id' => $id,
                          'value' => date('Y-m-d H:i:s'),
                         ];
                \Yii::$app->db->createCommand('UPDATE `users` SET lastlogin=:value WHERE id=:id;', $param)->execute();
            }
        }
    }
}
