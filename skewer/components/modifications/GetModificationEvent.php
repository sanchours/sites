<?php

namespace skewer\components\modifications;

use skewer\build\Design\CSSEditor\models\CssFiles;
use skewer\components\design\model\Params;
use yii\base\Event;

/**
 * Спец класс для сбора списка активных поисковых движков
 *      через событийную модель.
 */
class GetModificationEvent extends Event
{
    private $iMaxTime = 0;

    public function setLastTime($iTime)
    {
        if ($iTime > $this->iMaxTime) {
            $this->iMaxTime = $iTime;
        }
    }

    public function getLastTime()
    {
        $iMaxMod = $this->getMaxMod();

        if ($iMaxMod > $this->iMaxTime) {
            $this->iMaxTime = $iMaxMod;
        }

        return $this->iMaxTime;
    }

    /**
     * Выборка максимальной модификации из таблиц не попадающих под поиск
     * и не подходящих под событийную модель.
     */
    private function getMaxMod()
    {
        $aOut = [];

        $aCssParams = Params::find()
            ->orderBy(['updated_at' => SORT_DESC])
            ->asArray()
            ->one();

        $aOut[] = strtotime($aCssParams['updated_at']);

        $aCssFiles = CssFiles::find()
            ->orderBy(['last_upd' => SORT_DESC])
            ->asArray()
            ->one();

        $aOut[] = strtotime($aCssFiles['last_upd']);

        return max($aOut);
    }
}
