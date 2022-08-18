<?php

namespace skewer\build\Tool\LeftList;

use skewer\base\SysVar;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;
use skewer\components\i18n\Languages;
use yii\base\UserException;

/**
 * Родительский класс для модулей панели управления.
 */
abstract class ModulePrototype extends Cms\Tabs\ModulePrototype implements ModuleInterface
{
    public static function updateLanguage()
    {
        $sLanguage = \Yii::$app->i18n->getTranslateLanguage();
        $oLanguage = Languages::getByName($sLanguage);
        if (!$oLanguage || !$oLanguage->active) {
            $sLanguage = SysVar::get('language');
        }
        \Yii::$app->language = $sLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        self::updateLanguage();

        parent::onCreate();

        parent::init();
    }

    /**
     * Проверка доступа.
     */
    protected function checkAccess()
    {
        if (!CurrentAdmin::isSystemMode() and !CurrentAdmin::canDo('skewer\\build\\Tool\\Policy\\Module', 'useControlPanel')) {
            throw new UserException('Access denied to control panel for current user in module ' . $this->getModuleName());
        }
        CurrentAdmin::testUsedModule($this->getModuleName());
    }

    public function getName()
    {
        return $this->getModuleName();
    }
}
