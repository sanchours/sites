<?php

namespace skewer\build\Cms\Tabs;

use skewer\base\ui;

class SubModulePrototype
{
    protected $oModule;

    public function __construct(ModulePrototype $oModule)
    {
        $this->oModule = $oModule;

        $this->init();
    }

    /**
     * Функция выполняется при инициализации класса.
     */
    public function init()
    {
    }

    final protected function setInterface(ui\state\BaseInterface $oInterface)
    {
        $this->oModule->setInterface($oInterface);
    }

    /**
     * Устанавливает название панели.
     *
     * @param string $sNewName - новое имя
     * @param bool $bAddTabName - нужно ли добавить в начало имя вкладки
     */
    protected function setPanelName($sNewName, $bAddTabName = false)
    {
        $this->oModule->setPanelName($sNewName, $bAddTabName);
    }

    /**
     * Метод - исполнитель функционала.
     */
    public function execute()
    {
        return false;
    }

    /**
     * Функция получения защищенных параметров основного объекта.
     *
     * @param string $sParamName Имя параметра
     */
    public function getParam($sParamName)
    {
        return $this->oModule->getParam($sParamName);
    }

    /**
     * Функция сохранения защищенных параметров основного объекта.
     *
     * @param string $sParamName Имя параметра
     * @param $sValue
     */
    public function setParam($sParamName, $sValue)
    {
        return $this->oModule->setParam($sParamName, $sValue);
    }

    public function getInData()
    {
        return $this->oModule->getInData();
    }
}
