<?php

namespace skewer\components\auth;

/**
 * @class CurrentUser
 *
 * @author Andrew, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project skewer
 */
class CurrentUserPrototype
{
    /**
     * Область видимости.
     *
     * @var string
     */
    public static $sLayer = 'public';

    /**
     * Получить id текущего авторизованного пользователя.
     *
     * @static
     *
     * @return bool|int
     */
    public static function getId()
    {
        return Auth::getUserId(static::$sLayer);
    }

    /**
     * Получить id текущей политики доступа.
     *
     * @static
     *
     * @return bool|int
     */
    public static function getPolicyId()
    {
        return Auth::getPolicyId(static::$sLayer);
    }

    /**
     * Получить уровень для текущей политики доступа.
     *
     * @static
     *
     * @return bool|int
     */
    public static function getAccessLevel()
    {
        return Auth::getAccessLevel(static::$sLayer);
    }

    /**
     * Проверяет является ли пользователь обладатель ограниченных прав.
     *
     * @static
     *
     * @return bool
     */
    public static function isLimitedRights()
    {
        return (bool) (Auth::getAccessLevel(static::$sLayer) > 1);
    }

    /**
     * Получить массив свойств текущего пользователя (дополнительные поля).
     *
     * @param array $aFilterFields Массив возвращаемых полей
     *
     * @return array
     */
    public static function getProperties(array $aFilterFields = ['login', 'name', 'postcode', 'address', 'phone', 'user_info', 'reg_date', 'lastlogin'])
    {
        if ($aUserData = Auth::getUserData(static::$sLayer) and is_array($aUserData)) {
            return array_intersect_key($aUserData, array_flip($aFilterFields));
        }

        return [];
    }

    /**
     * Отдает true если пользователь авторизован.
     *
     * @return bool
     */
    public static function isLoggedIn()
    {
        return Auth::isLoggedIn(static::$sLayer);
    }

    // func

    /**
     * Проверяет доступен ли текущему пользователю раздел на запись.
     *
     * @static
     *
     * @param int $sectionId Id раздела
     *
     * @return bool
     */
    public static function canRead($sectionId)
    {
        return Auth::isReadable(static::$sLayer, $sectionId);
    }

    // func

    /**
     * Возвращает значение параметра функционального уровня политики доступа (группа+пользователь).
     *
     * @param $sModuleClassName string
     * @param $sParamName string
     * @param null $mDefValue mixed
     *
     * @return mixed
     */
    public function getModuleParam($sModuleClassName, $sParamName, $mDefValue = null)
    {
        return Auth::getModuleParam(static::$sLayer, $sModuleClassName, $sParamName, $mDefValue);
    }

    // func

    /**
     * Возвращает значение параметра функционального уровня политики доступа (группа+пользователь) приведенное
     * к булевому типу.
     *
     * @static
     *
     * @param string $moduleClassName Имя класса модуля
     * @param string $paramName Имя параметра
     * @param mixed $defValue Значение по-умолчанию если параметра с таким именем нет
     *
     * @return bool|mixed
     */
    public static function canDo($moduleClassName, $paramName, $defValue = null)
    {
        return Auth::canDo(static::$sLayer, $moduleClassName, $paramName, $defValue);
    }

    // func

    /**
     * Проверяет наличие запрашиваемого модуля в списке разрешенных модулей текущей политики.
     *
     * @param $moduleClassName
     *
     * @return bool
     */
    public static function canUsedModule($moduleClassName)
    {
        return Auth::canUsedModule(static::$sLayer, $moduleClassName);
    }

    /**
     * Возвращает массив разделов доступных для чтения текущему пользователю.
     *
     * @return array
     */
    public static function getReadableSections()
    {
        return Auth::getReadableSections(static::$sLayer);
    }

    // func

    public static function getUserData()
    {
        return Auth::getUserData(static::$sLayer);
    }

    // func

    public static function reloadPolicy()
    {
        return Auth::reloadPolicy(static::$sLayer);
    }

    // func

    public static function login($sLogin = '', $sPassword = '')
    {
        $sLogin = mb_strtolower($sLogin);

        return Auth::login(static::$sLayer, $sLogin, $sPassword);
    }

    // func

    public static function loginNetwork($login, $password, $network = '')
    {
        $login = mb_strtolower($login);

        return Auth::loginNetwork(static::$sLayer, $login, $password, $network);
    }

    /**
     * Выход авторизованного пользователя.
     *
     * @static
     *
     * @return bool
     */
    public static function logout()
    {
        return Auth::logout(static::$sLayer);
    }

    // func

    /**
     * Проверяет авторизованность пользователя.
     *
     * @return bool
     */
    public static function isAuthorized()
    {
        return (isset($_SESSION['auth'][static::$sLayer]['userData']['policyAlias']) and
            $_SESSION['auth'][static::$sLayer]['userData']['policyAlias'] != 'default') ? true : false;
    }

    /**
     * Отдает Login текущего пользователя.
     *
     * @return bool
     */
    public static function getLogin()
    {
        if (isset($_SESSION['auth'][static::$sLayer]['userData']['login'])) {
            return $_SESSION['auth'][static::$sLayer]['userData']['login'];
        }

        return false;
    }
} // class
