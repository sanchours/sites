<?php

namespace skewer\build\Tool\UnderConstruction;

use skewer\base\SysVar;
use skewer\components\auth\CurrentAdmin;

class Api
{
    const SYSVAR_UCONST = 'underconstruction.show';

    public static function getDataBlock()
    {
        return \Yii::t('data/uconst', 'under_construction', [], \Yii::$app->language);
    }

    public static function getInstallUC()
    {
        return SysVar::get(self::SYSVAR_UCONST);
    }

    public static function setInstallUC($bShow)
    {
        return SysVar::set(self::SYSVAR_UCONST, $bShow);
    }

    public static function isShowBlock()
    {
        $bType = self::getInstallUC();
        $bSys = CurrentAdmin::isSystemMode();

        return $bType && !$bSys;
    }
}
