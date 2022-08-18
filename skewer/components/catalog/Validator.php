<?php

namespace skewer\components\catalog;

class Validator
{
    const TYPE_SET = 1;
    const TYPE_UNIQUE = 2;

    /**
     * Отдает список имен валидаторов.
     */
    protected static function getListOfNames()
    {
        return ['set', 'unique'];
    }

    /**
     * Отдает полный список валидаторов в виде пар "имя валидатора" => "название".
     */
    public static function getListWithTitles()
    {
        $aOut = [];

        foreach (self::getListOfNames() as $sName) {
            $aOut[$sName] = \Yii::t('catalog', "validator_{$sName}");
        }

        return $aOut;
    }
}
