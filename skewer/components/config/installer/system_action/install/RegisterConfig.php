<?php
/**
 * @class RegisterConfig
 *
 * @author ArmiT
 * @date 24.01.14
 * @project canape
 */

namespace skewer\components\config\installer\system_action\install;

use skewer\components\config\ConfigUpdater;
use skewer\components\config\installer;

class RegisterConfig extends installer\Action
{
    public function init()
    {
    }

    public function execute()
    {
        ConfigUpdater::buildRegistry()->registerModule($this->module->moduleConfig);
    }

    public function rollback()
    {
        ConfigUpdater::buildRegistry()->removeModule($this->module->moduleName, $this->module->layer);
    }
}
