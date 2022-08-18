<?php

namespace skewer\modules\rest\controllers;

use skewer\build\Page\Auth\Api;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\Policy;

/**
 * Работа с пользователями через rest
 * Class UsersController.
 */
class UsersController extends PrototypeController
{
    /** Допустимые для редактирования поля информации о пользователе (без пробелов) */
    const EDITABLE_USER_FIELDS = 'name,postcode,address,phone,user_info';

    /** Ошибка авторизации */
    const ERR_AUTH = 'user_not_found';
    /** Для проведения операции необходимо авторизоваться */
    const ERR_NOAUTH = 'need_auth';
    /** Данные заполнены не верно */
    const ERR_DATA = 'not_valid_data';
    /** Пользователь с таким логином уже существует */
    const ERR_REGLOGIN = 'login_busy';

    /** Регистрация нового пользователя */
    public function actionRegister()
    {
        $aData = \Yii::$app->request->post();
        $sLogin = mb_strtolower(\Yii::$app->request->post('login', ''));
        // Отфильтровать служебные данные пользователей
        $aAllowedFields = explode(',', self::EDITABLE_USER_FIELDS . ',login,pass');
        $aData = array_intersect_key($aData, array_flip($aAllowedFields));

        // Попытка регистрации
        if (Api::registerUser($aData)) {
            return $this->showSuccess();
        }

        // Вывод ошибок регистрации
        if ($sLogin and Api::getUserByLogin($sLogin)) {
            return $this->showError(self::ERR_REGLOGIN);
        }

        return $this->showError(self::ERR_DATA);
    }

    /** Авторизация пользователя */
    public function actionAuth()
    {
        $sLogin = mb_strtolower(\Yii::$app->request->post('login', ''));
        $sPass = \Yii::$app->request->post('pass', '');

        // Запрос информации о текущем пользователе
        if (!$sLogin and !$sPass) {
            return self::isLogged() ? $this->showSuccess() : $this->showError(self::ERR_NOAUTH);
        }

        if (self::isLogged()) {
            CurrentUser::logout();
        }

        if (!$sLogin or !$sPass) {
            return $this->showError(self::ERR_DATA);
        }

        // Попытка авторизации
        if (CurrentUser::login($sLogin, $sPass)) {
            return $this->showSuccess();
        }

        return $this->showError(self::ERR_AUTH);
    }

    /** Редактирование данных пользователя и/или пароля + вывод информации о текущем пользователе */
    public function actionEditprofile()
    {
        $aData = \Yii::$app->request->post();
        $sPass = \Yii::$app->request->post('pass', '');
        // Отфильтровать возможность изменять служебные данные пользователей
        $aAllowedFields = explode(',', self::EDITABLE_USER_FIELDS);
        $aData = array_intersect_key($aData, array_flip($aAllowedFields));

        if ((self::isLogged()) and
             ($iUserId = CurrentUser::getId()) and
             ($oUser = Api::setUserData($aData, $iUserId))) {
            // Если пришли данные, то попытаться обновить
            if ($aData and !$oUser->save()) {
                return $this->showError(self::ERR_DATA);
            }

            // Если пришёл пароль, то изменить его
            $sPass and Api::saveNewPass($oUser, $sPass);

            if ($aData or $sPass) {
                // Если данные пользователя были изменены, то обновить данные авторизации всех параллельно авторизованных пользователей
                Policy::incPolicyVersion();
                // Обновить сессионные данные текущего пользователя
                Auth::loadUser(CurrentUser::$sLayer, $oUser->id);
            }

            return CurrentUser::getProperties();
        }

        return $this->showError(self::ERR_NOAUTH);
    }

    /** Восстановление пароля */
    public function actionRecoverypass()
    {
        $sLogin = mb_strtolower(\Yii::$app->request->post('email', '')); // Email = Login
        if (!$sLogin) {
            return $this->showError(self::ERR_DATA);
        }

        if (($oUser = Api::getUserByLogin($sLogin)) and
             (Api::recoverPass($oUser))) {
            return $this->showSuccess();
        }

        return $this->showError(self::ERR_OTHER);
    }

    /** Проверить состояние авторизации пользователя */
    public static function isLogged()
    {
        return  CurrentUser::isLoggedIn() and CurrentUser::getPolicyId() != Auth::getDefaultGroupId();
    }
}
