<?php
/**
 * @class RegisterConfig
 *
 * @author ArmiT
 * @date 24.01.14
 * @project canape
 */

namespace skewer\components\config\installer\system_action\reinstall;

use skewer\components\config\installer;
use skewer\components\i18n\Categories;

class UpdateLanguage extends installer\Action
{
    protected $updateCache = false;

    public function __construct(installer\Module $module, $updateCache = false)
    {
        parent::__construct($module);
        $this->updateCache = (bool) $updateCache;
    }

    public function init()
    {
    }

    public function execute()
    {
        Categories::updateModuleLanguageValues($this->module);

        if ($this->updateCache) {
            /* @noinspection PhpUndefinedMethodInspection */
            \Yii::$app->getI18n()->clearCache();
        }
    }

    public function rollback()
    {
    }
}
