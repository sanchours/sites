<?php
/**
 * @class CheckConsistency
 *
 * @author ArmiT
 * @date 24.01.14
 * @project canape
 */

namespace skewer\components\config\installer\system_action\uninstall;

use skewer\components\config\ConfigUpdater;
use skewer\components\config\installer;

class UnregisterConfig extends installer\Action
{
    protected $backupName = '';

    public function init()
    {
        $this->backupName = md5(
            $this->module->moduleName .
            $this->module->layer .
            dechex(random_int(0, 10000))
        );
    }

    public function execute()
    {
        ConfigUpdater::createBackup($this->backupName);
        ConfigUpdater::buildRegistry()->removeModule($this->module->moduleName, $this->module->layer);
    }

    public function rollback()
    {
        ConfigUpdater::recoverBackup($this->backupName);
    }
}
