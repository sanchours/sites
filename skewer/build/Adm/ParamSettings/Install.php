<?php

namespace skewer\build\Adm\ParamSettings;

use skewer\components\config\InstallPrototype;
use skewer\components\i18n\Languages;
use yii\helpers\ArrayHelper;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        /* Обойдем все параметры, вынесенные для редактирования в модуле и скроем их из редакторов */
        foreach (Languages::getAllActive() as $aLang) {
            $sLang = ArrayHelper::getValue($aLang, 'name');
            foreach (Api::getParameters($sLang) as $oParam) {
                if ($oParam->access_level != 0) {
                    $oParam->access_level = 0;
                    $oParam->save(false);
                }
            }
        }

        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
