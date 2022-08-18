<?php

namespace skewer\build\Tool\LeftList;

use skewer\base\site\Layer;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;

/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 26.07.12
 * Time: 13:14
 * To change this template use File | Settings | File Templates.
 */
class Module extends Cms\LeftPanel\ModulePrototype
{
    /**
     * Отдает класс-родитель, насдедники которого могут быть добавлены в дерево процессов
     * в качестве вкладок.
     *
     * @return string
     */
    public function getAllowedChildClassForTab()
    {
        return 'skewer\build\Tool\LeftList\ModuleInterface';
    }

    /**
     * Возвращает true, если модуль, доступный только в режиме системного администратора запрашивается из-под
     * политики более ограниченной в правах.
     *
     * @static
     *
     * @param $aItem
     *
     * @return bool
     */
    protected function checkModuleAccess($aItem)
    {
        // системному можно все
        if (CurrentAdmin::isSystemMode()) {
            return true;
        }

        // елси есть условие и оно ложно - пропустить
        if (isset($aItem['condition']) and !$aItem['condition']) {
            return false;
        }

        // остальным разрешить
        return true;
    }

    // func

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

    private function getModuleList()
    {
        $aOut = [];

        $aModuleList = \Yii::$app->register->getModuleList(Layer::TOOL);

        $aCurData = CurrentAdmin::getAvailableModules();

        foreach ($aModuleList as $sModuleName) {
            $oModuleConfig = \Yii::$app->register->getModuleConfig($sModuleName, Layer::TOOL);

            // не выводить себя
            if (get_class($this) === $oModuleConfig->getNameWithNamespace()) {
                continue;
            }

            if (CurrentAdmin::isSystemMode() || isset($aCurData[$oModuleConfig->getNameWithNamespace()]) || isset($aCurData[$oModuleConfig->getName()])) {
                $sTitle = $oModuleConfig->getTitle();

                $aOut[] = [
                    'id' => $oModuleConfig->getName(),
                    'name' => $oModuleConfig->getNameWithNamespace(),
                    'title' => $sTitle,
                    'group' => Group::getTitle($oModuleConfig->getVal('group')),
                ];
            }
        }

        uasort($aOut, [$this, 'orderModulesByTitle']);

        return array_values($aOut);
    }

    /**
     * Пользовательская функция сортировки списка модулей.
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    private function orderModulesByTitle($a, $b)
    {
        if (!isset($a['title']) or !isset($b['title'])) {
            return 0;
        }

        if ($a['title'] > $b['title']) {
            return 1;
        }
        if ($a['title'] < $b['title']) {
            return -1;
        }

        return 0;
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
        return [
            'forms' => [
                'enableSettings' => 1,
            ],
        ];
    }

    /**
     * Задает список модулей.
     *
     * @return int
     */
    public function actionInit()
    {
        // команда на инициализацию
        $this->setCmd('init');

        $this->addInitParam('title', $this->getTitle());

        // добавить библиотеку отображения
        $this->addLibClass('LeftListGrid');

        // запрос списка площадок
        $this->setData('items', $this->getModuleList());

        return psComplete;
    }
}
