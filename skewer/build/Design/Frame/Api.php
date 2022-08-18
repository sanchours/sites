<?php

namespace skewer\build\Design\Frame;

use skewer\base\site\Layer;

/**
 * Библиотека классов для дизайнерского режима.
 */
class Api
{
    /**
     * Отдает название модуля по имени.
     *
     * @static
     *
     * @param string $sModule
     *
     * @return string
     */
    public static function getModuleTitleByName($sModule)
    {
        if ($sModule === 'content') {
            return \Yii::t('adm', 'main_module_on_page');
        }

        if (\Yii::$app->register->moduleExists($sModule, Layer::PAGE)) {
            return \Yii::$app->register->getModuleConfig($sModule, Layer::PAGE)->getTitle();
        }

        return $sModule;
    }
}
