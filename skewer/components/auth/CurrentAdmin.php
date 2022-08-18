<?php

namespace skewer\components\auth;

use skewer\base\section\models\TreeSection;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Класс помощник для быстрого доступа к данным текущего
 * авторизованного пользователя !административной части.
 */
class CurrentAdmin extends CurrentUserPrototype
{
    public static $sLayer = 'admin';

    /**
     * Если текущий пользователь - системный администратор и не активен режим
     * "простого администратора", то возвращает true.
     * @static
     * @return bool
     */
    public static function isSystemMode()
    {
        return self::isSystemModeByUserData() and !self::isTempAdminMode();
    }

    /**
     * Закрытая функция для определения того, что пользователь является sys по чистым авторизационным данным
     * Для логики работы на в админке следует применять метод isSystemMode
     * @return bool
     */
    public static function isSystemModeByUserData() {
        return (bool)ArrayHelper::getValue(
            $_SESSION,
            ['auth', self::$sLayer, 'userData', 'systemMode'],
            false
        );
    }

    /**
     * Отдает true если активирован режим "обычного администроатора"
     * @return bool
     */
    public static function isTempAdminMode() {
        return (bool)ArrayHelper::getValue(
            $_SESSION,
            ['auth', self::$sLayer, 'tempAdminMode'],
            false
        );
    }

    /**
     * Устанавливает режим "обычного администроатора"
     * @param bool $val true для активации, false для деактивации
     */
    public static function setTempAdminMode($val) {
        if (!self::isSystemModeByUserData())
            return;
        ArrayHelper::setValue(
            $_SESSION,
            ['auth', self::$sLayer, 'tempAdminMode'],
            $val
        );
        Policy::incPolicyVersion();
    }

    /**
     * Возвращает id стартового раздела текущей админской политики.
     *
     * @static
     *
     * @return bool
     */
    public static function getMainSection()
    {
        return Auth::getMainSection(self::$sLayer);
    }

    /**
     * Возвращает true, если текущий пользователь системы является администратором либо системным администратором
     * или false в противном случае.
     *
     * @return bool
     */
    public static function isAdminPolicy()
    {
        /*Проверяем на sys*/
        if (self::isSystemMode()) {
            return true;
        }

        /*Проверяем на обычную авторизованность в слое*/
        return self::isAuthorized();
    }

    // func

    public static function canRead($sectionId)
    {
        if (static::isSystemMode()) {
            return true;
        }

        return Auth::isReadable(static::$sLayer, $sectionId);
    }

    // func

    public static function getReadableSections()
    {
        if (static::isSystemMode()) {
            $out = [];
            /** @var TreeSection[] $sections */
            $sections = TreeSection::find()->all();
            foreach ($sections as $section) {
                $out[] = $section->id;
            }

            return $out;
        }

        return (isset($_SESSION['auth'][static::$sLayer]['read_access'])) ? $_SESSION['auth'][static::$sLayer]['read_access'] : false;
    }

    // func

    public static function getAvailableModules()
    {
        return Auth::getAvailableModules(static::$sLayer);
    }

    /**
     * Проверяет права доступа к контрольной панели и если их нет - выкидывает исключение.
     *
     * @static
     *
     * @throws UserException
     */
    public static function testControlPanelAccess()
    {
        if (!self::isSystemMode() and !self::canDo('skewer\\build\\Tool\\Policy\\Module', 'useControlPanel')) {
            throw new UserException('Access denied to control panel for current user');
        }
    }

    /**
     * Проверяет можно ли использовать в дизайнерский режим
     *
     * @static
     */
    public static function allowDesignAccess()
    {
        return self::isSystemMode() or self::canDo('skewer\\build\\Tool\\Policy\\Module', 'useDesignMode');
    }

    public static function testUsedModule($sModuleName)
    {
        if (!self::isSystemMode() && !self::canUsedModule($sModuleName)) {
            throw new UserException('Access denied!');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function logout()
    {
        Yii::$app->i18n->admin->clearLang();

        return parent::logout();
    }

    /**
     * Изменяет флаг сбрасывать/несбрасывать кэш для текущего пользователя из сессии
     */
    public static function changeCacheMode()
    {
        Yii::$app->session->set('unsetCache', !(bool)Yii::$app->session->get('unsetCache'));
    }

    /**
     * Возвращает состояния сбрасывать/несбрасывать кэш для текущего пользователя
     * @return bool
     */
    public static function getCacheMode()
    {
        return (bool)Yii::$app->session->get('unsetCache');
    }

    /**
     * Изменяет флаг показывать/скрывать режим отладки для текущего пользователя
     */
    public static function changeDebugMode()
    {
        Yii::$app->session->set('debugMode4User', !(bool)Yii::$app->session->get('debugMode4User'));
    }

    /**
     * Возвращает флаг показывать/скрывать режим отладки для текущего пользователя
     * @return bool
     */
    public static function getDebugMode()
    {
        return (bool)Yii::$app->session->get('debugMode4User');
    }

}// class
