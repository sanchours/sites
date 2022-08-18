<?php

namespace skewer\build\Design\Inheritance;

use skewer\build\Cms;
use skewer\components\design\Design;
use skewer\components\design\DesignManager;

/**
 * Модуль редактирования шаблонов настроек дизайнерского режима
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /** @var string режим отображения (по-умолчанию или pda версия) */
    protected $sViewMode = Design::versionDefault;

    /**
     * Список шаблонов и исключений.
     */
    protected function actionInit()
    {
        $this->addLibClass('InheritanceRefs');
        $this->setData('exceptions', Api::getParamLinks());
        $this->setCmd('init');
    }

    /**
     * Восстанавливает связь с базовыми настройками.
     */
    protected function actionSetLinkActive()
    {
        $id = $this->getInt('id');
        $active = $this->getInt('active');

        DesignManager::setActiveParamRefs($id, $active);

        $this->fireJSEvent('reload_show_frame');
        $this->fireJSEvent('reload_param_editor');
        $this->fireJSEvent('reload_inheritance');
        \Yii::$app->clearAssets();

        $this->actionInit();
    }
}
