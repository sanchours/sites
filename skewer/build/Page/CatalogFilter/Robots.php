<?php

namespace skewer\build\Page\CatalogFilter;

use skewer\base\site\RobotsInterface;
use skewer\components\catalog\Card;
use skewer\components\filters;

class Robots implements RobotsInterface
{
    public static function getRobotsDisallowPatterns()
    {
        $aResult = [];

        $iFilterType = filters\Api::getFilterType();

        $aCards = Card::getGoodsCards(false);

        foreach ($aCards as $oCard) {
            $oFilter = filters\FilterPrototype::getInstanceByType($iFilterType);
            $oFilter->initFields($oCard->name);
            $aRobotsPatterns = $oFilter->getRobotsDisallowPatterns();
            $aResult = array_merge($aResult, $aRobotsPatterns);
        }

        return $aResult;
    }

    public static function getRobotsAllowPatterns()
    {
        return false;
    }
}
