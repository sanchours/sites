<?php

namespace skewer\build\Design\Header;

use skewer\base\log\models\Log;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;

/**
 * Модуль верхней панели дизайнерского режима
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /**
     * Первичная инициализация.
     *
     * @return int
     */
    public function actionInit()
    {
    }

    /**
     * Выход из системы.
     *
     * @return int
     */
    protected function actionLogout()
    {
        // задать состояние
        $this->setCmd('logout');

        Log::addNoticeReport('Выход пользователя из системы администрирования', Log::buildDescription(['ID пользователя' => CurrentAdmin::getId(), 'Логин' => CurrentAdmin::getLogin()]), Log::logUsers, $this->getModuleName());

        // попытка авторизации
        $bLogOut = CurrentAdmin::logout();

        // результат авторизации
        $this->setData('success', $bLogOut);

        // отдать результат работы метода
        return psComplete;
    }

    protected function actionDropCacheAndReload()
    {
        \Yii::$app->rebuildCss();
        $this->fireJSEvent('reload_display_form');
    }
}
