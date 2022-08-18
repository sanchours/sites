<?php

namespace skewer\components\auth;

/**
 * Класс помощник для быстрого доступа к данным текущего
 * авторизованного пользователя !клиентской части.
 */
class CurrentUser extends CurrentUserPrototype
{
    public static $sLayer = 'public';

    /**
     * Отдает true если пользователь авторизован
     * При этом если он находится под дефолтной политикой, то это за авторизацию не считается
     * (она выдается при любом заходе по умолчанию, чтобы автивировать стандартные механизымы
     * авторизации для работы на сайте).
     *
     * @return bool
     */
    public static function isLoggedIn()
    {
        return  parent::isLoggedIn() && CurrentUser::getPolicyId() != Auth::getDefaultGroupId();
    }
}// class
