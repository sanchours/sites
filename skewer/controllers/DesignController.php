<?php

namespace skewer\controllers;

use skewer\base\log\models\Log;
use skewer\build\Tool\Utils;
use skewer\components\auth\CurrentAdmin;
use skewer\components\design\DesignManager;
use skewer\components\design\Template;
use skewer\components\regions\RegionHelper;

/**
 * Контроллер для дизайнерского режима.
 */
class DesignController extends CmsPrototype
{
    /**
     * @throws \Exception
     * @throws \skewer\components\config\Exception
     *
     * @return bool|string
     */
    public function actionIndex()
    {
        if (RegionHelper::isInstallModuleRegion()) {
            RegionHelper::checkRegion();
        }

        return $this->runApplication();
    }

    public function actionReset()
    {
        if (CurrentAdmin::isLoggedIn()) {
            // очищаем все таблицы с css настройками
            DesignManager::clearCSSTables();
            Utils\Api::dropCache();

            Template::change('head', 'base', true);
            Template::change('footer', 'base', true);

            // записываем в лог факт сброса дизайна
            Log::addNoticeReport(\Yii::t('adm', 'reseted'), '', Log::logUsers, '');

            // выводим сообщение
            \Yii::$app->i18n->setTranslateLanguage('ru');
            echo \Yii::t('adm', 'reseted');
        }
    }

    /**
     * Отдает имя ключа для сессионного хранилища.
     *
     * @return string
     */
    protected function getSessionKeyName()
    {
        return 'designKey';
    }

    /**
     * Возвращает имя модуля основного слоя.
     *
     * @return string
     */
    public function getLayoutModuleName()
    {
        return 'skewer\build\Design\Layout\Module';
    }

    /**
     * Возвращает имя модуля авторизации.
     *
     * @return string
     */
    public function getAuthModuleName()
    {
        return 'skewer\build\Cms\Auth\Module';
    }

    /**
     * Возвращает имя первично инициализируемого модуля.
     *
     * @return string
     */
    public function getFrameModuleName()
    {
        return 'skewer\build\Design\Frame\Module';
    }

    /**
     * Возвращает имя первично инициализируемого модуля при отсутствии авторизации.
     *
     * @return string
     */
    public function getFrameAuthModuleName()
    {
        return 'skewer\build\Cms\Frame\Module';
    }

    /**
     * Отдает базовый url для сервиса.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return '/design/';
    }

    /**
     * Инициализация языков
     * Метод перекрыт, поскольку дизайнерская часть не переводилась и чтобы
     * не было "залетных" меток с других языков сделали принудительно основной.
     */
    protected function initLanguage()
    {
        \Yii::$app->i18n->setTranslateLanguage('ru');
    }
}
