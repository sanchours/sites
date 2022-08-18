<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 16.05.2016
 * Time: 16:01.
 */
use skewer\components\tokensAuth\Api as Api;
use skewer\components\tokensAuth\Config as Config;
use skewer\components\tokensAuth\DB as DB;
use skewer\components\tokensAuth\Request as Request;
use skewer\components\tokensAuth\Response as Response;
use skewer\components\tokensAuth\Session as Session;

//для 3
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

Config::init();

Request::setRequest();

$sCmd = Request::getValByKey('cmd', 'getToken');

//unset($_SESSION['auth']['admin']);
if ((Session::checkSession()) and ($sCmd !== 'checkToken')) {
    $sReturnLink = Request::getValByKey('return_link', 'none');
    $_SESSION['need_access_to'] = str_replace('%23', '#', $sReturnLink);

    Response::$sMode = 'redirect';

    if (isset($_SESSION['need_access_to']) && $_SESSION['need_access_to'] !== 'none') {
        Response::$aData['redirect_link'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SESSION['need_access_to'];
    } else {
        Response::$aData['redirect_link'] = 'http://' . $_SERVER['HTTP_HOST'] . '/admin/';
    }

    $sCmd = '';
}

/*Первое обращение к скрипту. Зарегистрируем его в сервисе токенов*/
if ((DB::getAppKey() == 'no_key' || DB::getPublicKey() == 'key') && $sCmd !== 'setKey') {
    Api::getKey();
}

DB::removeSys();

switch ($sCmd) {
    case 'setKey':
        Api::setKey();
        break;

    case 'getSysVersion':
        Api::getSysVersion();
        break;

    case 'getToken':
        Api::checkUpdates();
        Api::getToken();
        break;

    case 'checkToken':
        //Проверка постоянного токена. Подтверждается на сервисе токенов парой ключ приложения и собственно токеном
        Api::checkToken();
        break;

    case 'killSession':
        /*К удалению сессии мы допустим только того кто пришел с верным ключом приложения*/
        if (Request::getValByKey('app_key', '') === hash('sha512', DB::getAppKey() . str_replace('www.', '', $_SERVER['HTTP_HOST']))) {
            Api::killSession();
        }
        break;

    case 'error':
    default:

        break;
}

Response::execute();
