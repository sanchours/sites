<?php

namespace skewer\build\Tool\SeoGen\importer;

use yii\base\Event;

class GetListImportersEvent extends Event
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
     * Добавление класса importer в список доступных.
     *
     * @param string $sClassName - имя класса
     */
    public function addImporter($sClassName)
    {
        /** @var Prototype $oImporter */
        $oImporter = new $sClassName();
        $this->aList[$oImporter->getName()] = $sClassName;
    }
}
