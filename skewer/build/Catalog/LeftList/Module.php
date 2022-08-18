<?php

namespace skewer\build\Catalog\LeftList;

use skewer\base\site\Layer;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;

/**
 * Модуль для вывода списка категорий
 * Class Module.
 */
class Module extends Cms\LeftPanel\ModulePrototype
{
    /**
     * Отдает название модуля.
     */
    private function getModuleTitle()
    {
        return $this->title;
    }

    /**
     * Отдает класс-родитель, наследники которого могут быть добавлены в дерево процессов
     * в качестве вкладок.
     *
     * @return string
     */
    public function getAllowedChildClassForTab()
    {
        return 'skewer\build\Catalog\LeftList\ModuleInterface';
    }

    /**
     * Задает список модулей.
     */
    public function actionInit()
    {
        // команда на инициализацию
        $this->setCmd('init');

        $this->addInitParam('title', $this->getModuleTitle());

        // добавить библиотеку отображения
        $this->addLibClass('LeftListGrid');

        // запрос списка площадок
        $this->setData('items', $this->getModuleList());
    }

    /**
     * Отдает инициализационный массив для набора вкладок.
     *
     * @param int|string $mRowId идентификатор записи
     *
     * @return string[]
     */
    public function getTabsInitList($mRowId)
    {
        foreach ($this->getModuleList() as $aItem) {
            if ($aItem['id'] === $mRowId) {
                return [$aItem['id'] => $aItem['name']];
            }
        }

        return [];
    }

    /**
     * Отдает инициализационный массив для набора вкладок.
     *
     * @return string[]
     */
    private function getAvaliableModules()
    {
        $aList = ['Goods', 'CardEditor', 'Dictionary'];

        if (\Yii::$app->register->moduleExists('Collections', Layer::CATALOG)) {
            $aList[] = 'Collections';
        }

        $aList[] = 'ViewSettings';

        if (\Yii::$app->register->moduleExists('Settings', Layer::CATALOG)) {
            if (CurrentAdmin::isSystemMode()) {
                $aList[] = 'Settings';
            }
        }
        if (\Yii::$app->register->moduleExists('Filters', Layer::CATALOG)) {
            if (CurrentAdmin::isSystemMode()) {
                $aList[] = 'Filters';
            }
        }

        return $aList;
    }

    private function getModuleList()
    {
        $aOut = [];

        foreach ($this->getAvaliableModules() as $sModuleName) {
            $oModuleConfig = \Yii::$app->register->getModuleConfig($sModuleName, Layer::CATALOG);

            // не выводить себя
            if (get_class($this) === $oModuleConfig->getNameWithNamespace()) {
                continue;
            }

            $sTitle = $oModuleConfig->getTitle();

            $aOut[] = [
                'id' => $oModuleConfig->getName(),
                'name' => $oModuleConfig->getNameWithNamespace(),
                'title' => $sTitle,
                ];
        }

        return array_values($aOut);
    }

    /**
     * Задает дополнительные параметры для вкладок.
     *
     * @static
     *
     * @param $mRowId
     *
     * @return array
     */
    public function getTabsAddParams($mRowId)
    {
        $aOut = [];

        foreach ($this->getTabsInitList($mRowId) as $sKey => $sModule) {
            $aOut[$sKey]['sectionId'] = $mRowId;
        }

        return $aOut;
    }

    /**
     * Просто возвращает данные для выбора раздела.
     */
    protected function actionSelectNode()
    {
        // целевой раздел
        $iToSection = $this->getInt('sectionId');

        // отдать в вывод, если найдено
        $this->setCmd('selectNode');
        $this->setData('sectionId', $iToSection);
    }
}
