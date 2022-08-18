<?php

namespace skewer\components\GalleryOnPage;

use yii\base\Event;

class GetGalleryEvent extends Event
{
    /** @var string[] собранный список движков */
    private $aList = [];

    /**
     * Добавляет в список класс поискового движка.
     *
     * @param string $sClassName имя класса для вызова
     * @param string $sName
     */
    public function addGallery($sClassName, $sName = null)
    {
        if ($sName === null) {
            /** @var Prototype $s */
            $s = new $sClassName();
            $sName = $s->getName();
        }
        $this->aList[$sName] = $sClassName;
    }

    public function addGalleryList($aClasses)
    {
        foreach ($aClasses as $sClass) {
            $s = new $sClass();
            $sName = $s->getName();
            $this->aList[$sName] = $sClass;
        }
    }

    /**
     * Отадет массив собранных поисковых движков в формате
     *      псевдоним => имя класса.
     *
     * @return string[]
     */
    public function getList()
    {
        return $this->aList;
    }
}
