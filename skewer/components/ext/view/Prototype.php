<?php

namespace skewer\components\ext\view;

use skewer\base\ui;
use skewer\build\Cms\Tabs\ModulePrototype;
use yii\base\BaseObject;

/**
 * Класс прототип для типовых видов адмиских интерфейсов.
 */
abstract class Prototype extends BaseObject
{
    /**
     * Ссылка на мызвавший модуль.
     *
     * @var ModulePrototype
     */
    protected $_module;

    /**
     * Отработка перед выполнением сборки интерфейса.
     */
    public function beforeBuild()
    {
    }

    /**
     * Выполняет сборку интерфейса.
     */
    abstract public function build();

    /**
     * Отработка после сборки интерфейса.
     */
    public function afterBuild()
    {
    }

    /**
     * Отдает объект построитель интерфейса.
     *
     * @return ui\state\BaseInterface
     */
    abstract public function getInterface();

    /**
     * Задает ссылку на вызвавший модуль.
     *
     * @param ModulePrototype $oModule
     */
    public function setModule(ModulePrototype &$oModule)
    {
        $this->_module = &$oModule;
    }
}
