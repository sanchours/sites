<?php

namespace skewer\build\Cms\LeftPanel;

use skewer\build\Cms;

/**
 * Используется как прототип объектов для левой части админского интерфейса
 * Выводит дерево/список и для выбранного элемента может отдать набор вкладок.
 */
abstract class ModulePrototype extends Cms\Frame\ModulePrototype
{
    /**
     * Задает дополнительные параметры для вкладок.
     *
     * @static
     *
     * @param $mRowId
     *
     * @return array
     */
    public function getTabsAddParams(/* @noinspection PhpUnusedParameterInspection */$mRowId)
    {
        return [];
    }

    /**
     * Отдает инициализационный массив для набора вкладок.
     *
     * @abstract
     *
     * @param int|string $mRowId идентификатор записи
     *
     * @return string[]
     */
    abstract public function getTabsInitList($mRowId);

    /**
     * Отдает флаг фозможности создания наследников для данного модуля
     * в дереве процессов.
     *
     * @return bool
     */
    final protected function canBeParent()
    {
        return false;
    }

    /**
     * Отдает класс-родитель, насдедники которого могут быть добавлены в дерево процессов
     * в качестве вкладок.
     *
     * @return string
     */
    public function getAllowedChildClassForTab()
    {
        return 'skewer\build\Cms\Tabs\ModulePrototype';
    }
}
