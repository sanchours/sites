<?php

namespace skewer\build\Page\Auth;

use skewer\base\log\Logger;
use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\base\ui\ARSaveException;
use skewer\components\auth\Auth;
use skewer\components\auth\models\Users;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Mailer;
use yii\helpers\ArrayHelper;

class Api
{
    /** активное состояние пользователя*/
    const STATUS_NO_AUTH = 0;
    const STATUS_AUTH = 1;
    const STATUS_BANNED = 2;
    /** статусы активации пользователей в админке*/
    const ACTIVATE_AUTO = 1;
    const ACTIVATE_EMAIL = 2;
    const ACTIVATE_ADMIN = 3;

    /**
     * Путь страницы личного кабинета.
     *
     * @return mixed|string
     */
    public static function getProfilePath()
    {
        return ArrayHelper::getValue(Tree::getCachedSection(), \Yii::$app->sections->getValue('profile') . '.alias_path', '');
    }

    /**
     * Путь страницы авторизации.
     *
     * @return mixed|string
     */
    public static function getAuthPath()
    {
        return ArrayHelper::getValue(Tree::getCachedSection(), \Yii::$app->sections->getValue('auth') . '.alias_path', '');
    }

    /**
     * Получить урл страницы регистрации.
     *
     * @return string
     */
    public static function getUrlRegisterPage()
    {
        $str = sprintf('[%d][Auth?cmd=register]', \Yii::$app->sections->auth());

        return \Yii::$app->router->rewriteURL($str);
    }

    /**
     * Получить урл страницы восстановления пароля.
     *
     * @return string
     */
    public static function getUrlRecoverPage()
    {
        $str = sprintf('[%d][Auth?cmd=recover]', \Yii::$app->sections->auth());

        return \Yii::$app->router->rewriteURL($str);
    }

    /**
     * список режимов активации.
     *
     * @return array
     */
    public static function getActivateStatusList()
    {
        return [
            1 => \Yii::t('auth', 'activate_auto'),
            2 => \Yii::t('auth', 'activate_confirm'),
            3 => \Yii::t('auth', 'activate_admin'),
        ];
    }

    /**
     * значения политик доступа пользователя.
     *
     * @return array
     */
    public static function getStatusList()
    {
        return [
            0 => \Yii::t('auth', 'status_no_auth'),
            1 => \Yii::t('auth', 'status_auth'),
            2 => \Yii::t('auth', 'status_banned'),
        ];
    }

    /**
     * Регистрация нового пользователя.
     *
     * @param array $aData Массив данных пользователя
     * @param string $sError Текст ошибки решистрации
     *
     * @return bool;
     */
    public static function registerUser($aData)
    {
        $oItem = self::setUserData($aData);

        if (!$oItem->validate()) {
            throw new ARSaveException($oItem);
        }

        // генерим токен
        $oItem->active = 0;

        $iStatus = SysVar::get('auth.activate_status');
        $oItem->pass = Auth::buildPassword($oItem->login, $oItem->pass);
        //если автоматическая регистрация, то выставляем сразу активность
        $oItem->active = ($iStatus == Api::ACTIVATE_AUTO) ? Api::STATUS_AUTH : Api::STATUS_NO_AUTH;

        if (!$oItem->save()) {
            Logger::error('Ошибка сохранения пользователя');
            Logger::error($oItem->getAttributes());
            Logger::error($oItem->getErrors());
            return false;
        }

        $oTicket = new AuthTicket();
        $oTicket->setModuleName('auth');
        $oTicket->setActionName('activate');
        $oTicket->setObjectId($oItem->id);
        $sToken = $oTicket->insert();

        $aParams = [];

        // активация по e-mail
        if ($iStatus == Api::ACTIVATE_EMAIL) {
            $sBody = ModulesParams::getByName('auth', 'mail_user_activate');

            $aParams['link'] = Site::httpDomain() . Api::getAuthPath() . '?cmd=AccountActivation&token=' . $sToken;

            Mailer::sendMail($oItem->email, ModulesParams::getByName('auth', 'mail_title_user_newuser'), $sBody, $aParams);
        }

        $sBody = ModulesParams::getByName('auth', 'mail_admin_activate');

        $aParams['link'] = Site::admUrl('Auth');

        Mailer::sendMailAdmin(ModulesParams::getByName('auth', 'mail_title_admin_newuser'), $sBody, $aParams);

        return true;
    }

    /**
     * Установить данные пользователя.
     *
     * @param array $aData Новые данные пользователя
     * @param int $iUserId Id пользователя
     *
     * @return bool|Users
     */
    public static function setUserData($aData, $iUserId = 0)
    {
        $oUser = $iUserId ? PageUsers::findOne($iUserId) : new PageUsers();
        if (!$oUser) {
            return false;
        }

        foreach ($aData as $sField => $sVal) {
            $oUser->{$sField} = (($sField == 'email') || ($sField == 'login')) ? mb_strtolower($sVal) : $sVal;
        }

        return $oUser;
    }

    /**
     * Получение пользователя по логину.
     *
     * @param string $sLogin
     *
     * @return PageUsers $oUser
     */
    public static function getUserByLogin($sLogin)
    {
        $sLogin = mb_strtolower($sLogin);
        $oUser = PageUsers::findOne(['login' => $sLogin]);

        return $oUser;
    }

    /**
     * Получение пользователя по email.
     *
     * @param string $sEmail
     *
     * @return PageUsers $oUser
     */
    public static function getUserByEmail($sEmail)
    {
        $sEmail = mb_strtolower($sEmail);
        $oUser = PageUsers::findOne(['email' => $sEmail]);

        return $oUser;
    }

    /**
     * Генерация токена и отправка сообщения о возможности смены пароля.
     *
     * @param PageUsers $oUser
     *
     * @return bool
     */
    public static function recoverPass(PageUsers $oUser)
    {
        $oTicket = new AuthTicket();
        $oTicket->setModuleName('auth');
        $oTicket->setActionName('recover_pass');
        $oTicket->setObjectId($oUser->id);

        $sToken = $oTicket->insert();

        $bRes = $oUser->email and $oUser->save();

        if ($bRes) {
            $sBody = \Yii::t('data/auth', 'mail_reset_password');
            $aParams['link'] = Site::httpDomain() . self::getAuthPath() . '?cmd=newPassForm&token=' . $sToken;
            Mailer::sendMail($oUser->email, \Yii::t('data/auth', 'mail_title_reset_password'), $sBody, $aParams);
        }

        return $bRes;
    }

    /**
     * Сохранение нового пароля и сброс токена.
     *
     * @param Users $oUser
     * @param $sPassword
     *
     * @return bool
     */
    public static function saveNewPass(Users $oUser, $sPassword)
    {
        $oUser->pass = Auth::buildPassword($oUser->login, $sPassword);

        $bRes = $oUser->save();

        // send mail
        if ($bRes) {
            $bRes = Mailer::sendMail(
                $oUser->email,
                \Yii::t('data/auth', 'mail_title_new_pass'),
                \Yii::t('data/auth', 'mail_new_pass')
            );
        }

        return $bRes;
    }

    /**
     * Активация аккаунта.
     *
     * @param PageUsers $oUser
     *
     * @return bool
     */
    public static function accountActivate(PageUsers $oUser)
    {
        $oUser->active = 1;

        if ($oUser->save()) {
            $sBody = static::getTextMailActivate();

            Mailer::sendMail($oUser->email, ModulesParams::getByName('auth', 'mail_title_mail_activate'), $sBody);

            return true;
        }

        return false;
    }

    /**
     * получить текст письма уведамления для активации пользователя.
     */
    public static function getTextMailActivate()
    {
        return ModulesParams::getByName('auth', 'mail_activate');
    }

    /**
     * получить текст письма уведамления для снятия бана.
     */
    public static function getTextMailCloseBan()
    {
        return ModulesParams::getByName('auth', 'mail_close_ban');
    }

    /**
     * получить текст письма уведамления о блокировании.
     */
    public static function getTextMailBanned()
    {
        return ModulesParams::getByName('auth', 'mail_banned');
    }
}
