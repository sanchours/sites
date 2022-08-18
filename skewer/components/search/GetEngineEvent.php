<?php

namespace skewer\components\search;

use yii\base\Event;

/**
 * Спец класс для сбора списка активных поисковых движков
 *      через событийную модель.
 */
class GetEngineEvent extends Event
{
    /** @var string[] собранный список движков */
    private $aList = [];

    /**
     * Добавляет в список класс поискового движка.
     *
     * @param string $sClassName имя класса для вызова
     * @param string $sName
     */
    public function addSearchEngine($sClassName, $sName = null)
    {
        if ($sName === null) {
            /** @var Prototype $s */
            $s = new $sClassName();
            $sName = $s->getName();
        }
        $this->aList[$sName] = $sClassName;
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
