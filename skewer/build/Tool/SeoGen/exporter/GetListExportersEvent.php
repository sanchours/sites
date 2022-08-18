<?php

namespace skewer\build\Tool\SeoGen\exporter;

use yii\base\Event;

class GetListExportersEvent extends Event
{
    /** @var array */
    protected $aList = [];

    /**
     * @return array
     */
    public function getList()
    {
        return $this->aList;
    }

    /**
     * Добавление класса exporter в список доступных.
     *
     * @param string $sClassName - имя класса
     */
    public function addExporter($sClassName)
    {
        /** @var Prototype $oExporter */
        $oExporter = new $sClassName();
        $this->aList[$oExporter->getName()] = $sClassName;
    }
}
