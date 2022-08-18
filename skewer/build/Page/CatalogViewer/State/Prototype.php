<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\base\SysVar;
use skewer\build\Page\CatalogViewer;
use skewer\components\rating\Rating;

abstract class Prototype
{
    /** @var \skewer\build\Page\CatalogViewer\Module */
    protected $oModule;

    /** @var bool Флаг вывода формы фильтрации */
    protected $bShowFilter = true;

    public function __construct(CatalogViewer\Module $oModule)
    {
        $this->oModule = $oModule;
    }

    protected function getModuleField($sFieldName, $mDef = null)
    {
        return $this->oModule->getModuleField($sFieldName, $mDef);
    }

    /**
     * Отдает id текущего раздела.
     *
     * @return int
     */
    protected function sectionId()
    {
        return $this->oModule->sectionId();
    }

    /**
     * Получение имени группы в которой находится метка модуля.
     *
     * @return string
     */
    protected function getModuleGroup()
    {
        return $this->oModule->getLabel();
    }

    protected function getModule()
    {
        return $this->oModule;
    }

    abstract public function init();

    abstract public function build();

    final public function show()
    {
        $this->init();
        $this->build();
    }

    protected function getSection()
    {
        return $this->getModule()->sectionId();
    }

    public function showFilter()
    {
        return $this->bShowFilter;
    }

    /**
     * Добавить систему рейтинга для объекта/объектов.
     *
     * @param array $paObjects Список объектов или один объект
     * @param bool $bDisallowRate Запретить голосование?
     */
    final public function addRating(array &$paObjects, $bDisallowRate = true)
    {
        if (!$paObjects or !SysVar::get('catalog.show_rating')) {
            return;
        }

        if (isset($paObjects['id'])) {
            $paObjectsList = [&$paObjects];
        } else {
            $paObjectsList = &$paObjects;
        }

        foreach ($paObjectsList as &$paObject) {
            if (!isset($paObject['Rating'])) {
                $paObject['Rating'] = (new Rating($this->oModule->getModuleName()))->parse($paObject['id'], $bDisallowRate);
            }
        }
    }
}
