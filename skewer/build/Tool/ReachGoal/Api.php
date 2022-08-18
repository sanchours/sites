<?php

namespace skewer\build\Tool\ReachGoal;

use skewer\components\targets\models\TargetSelectors;

class Api
{
    public static function checkTarget(\skewer\components\targets\CheckTarget $target)
    {
        $aMatches = TargetSelectors::find()->where(['name' => $target->sName])->asArray()->all();

        foreach ($aMatches as $item) {
            $target->addCheckTarget(\Yii::t('ReachGoal', 'tab_name') . ' : ' . $item['selector']);
        }
    }

    public static function className()
    {
        return get_called_class();
    }
}
