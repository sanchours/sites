<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 24.04.2017
 * Time: 10:12.
 */

namespace skewer\build\Design\ParamTree;

use skewer\components\design\model\Groups;

class Api
{
    public static function getAllParents($iId, $aAllParents = [])
    {
        $aAllParents[] = (int) $iId;

        $aParent = Groups::find()
            ->where(['id' => $iId])
            ->asArray()
            ->one();

        if ($aParent['parent']) {
            $aAllParents = self::getAllParents($aParent['parent'], $aAllParents);
        }

        return $aAllParents;
    }

    public static function getAllChildrens($iId, $aAllChildrens = [])
    {
        $aAllChildrens[] = $iId;
        $aChilds = Groups::find()
            ->where(['parent' => $iId])
            ->asArray()
            ->all();

        foreach ($aChilds as &$child) {
            $aAllChildrens = array_merge($aAllChildrens, self::getAllChildrens($child['id'], $aAllChildrens));
        }

        return array_unique($aAllChildrens);
    }
}
