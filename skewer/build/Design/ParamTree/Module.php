<?php

namespace skewer\build\Design\ParamTree;

use skewer\build\Cms;
use skewer\components\design\Design;
use skewer\components\design\DesignManager;
use skewer\components\design\model\Groups;
use skewer\components\design\model\Params;

/**
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /** @var string режим отображения (по-умолчанию или pda версия) */
    protected $sViewMode = Design::versionDefault;

    /**
     * Состояние. Выбор дерева групп
     *
     * @return bool
     */
    protected function actionInit()
    {
        $this->addInitParam('lang', ['treePanelHeader' => \Yii::t('tree', 'treePanelHeader')]);

        // устновитьь состояние
        $this->setCmd('init');

        // отдать набор групп
        $this->setData('items', DesignManager::getAllGroupsAsTree());

        return true;
    }

    /**
     * Проверка состояния.
     */
    protected function actionCheckVersion()
    {
        // текущая версия в клиентской части
        $nowVersion = $this->getStr('ver', Design::versionDefault);

        // url открытой страницы
        $nowUrl = $this->getStr('url');

        // новая версия из url
        $newVersion = Design::getVersionByUrl($nowUrl);

        $this->sViewMode = $newVersion;

        if ($nowVersion !== $newVersion) {
            // отдать набор групп
            $this->actionInit();

            // переустновить состояние
            $this->setCmd('loadItems');

            // отдать новую версию
            $this->setData('newVersion', $newVersion);
        }
    }

    protected function actionRemoveGroup()
    {
        // входной набор эдементов
        $iId = $this->getInt('id');

        $aChildrens = Api::getAllChildrens($iId);

        Groups::deleteAll(
            ['id' => $aChildrens]
        );

        Params::deleteAll(
            ['group' => $aChildrens]
        );

        $this->actionInit();

        $this->fireJSEvent('reload_all');
        \Yii::$app->clearAssets();
    }
}
