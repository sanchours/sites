<?php
/**
 * @class UnregisterLanguage
 *
 * @author ArmiT
 * @date 24.01.14
 * @project canape
 */

namespace skewer\components\config\installer\system_action\uninstall;

use skewer\components\config\installer;
use skewer\components\i18n\Categories;

class UnregisterLanguage extends installer\Action
{
    public function init()
    {
    }

    public function execute()
    {
        /* @noinspection PhpUndefinedMethodInspection */
        \Yii::$app->getI18n()->clearCache();
    }

    public function rollback()
    {
        Categories::updateModuleLanguageValues($this->module);
        /* @noinspection PhpUndefinedMethodInspection */
        \Yii::$app->getI18n()->clearCache();
    }
}
