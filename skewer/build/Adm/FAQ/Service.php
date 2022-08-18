<?php

namespace skewer\build\Adm\FAQ;

use skewer\base\orm;
use skewer\base\site\ServicePrototype;

class Service extends ServicePrototype
{
    /**
     * @param orm\ActiveRecord $oItem
     *
     * @return string
     */
    public static function getStatusValue($oItem)
    {
        $aStatulList = Api::getStatusList();

        if (isset($aStatulList[$oItem['status']])) {
            return $aStatulList[$oItem['status']];
        }

        return '';
    }
}
