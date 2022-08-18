<?php
/**
 * @class InstallDependency
 *
 * @author ArmiT
 * @date 24.01.14
 * @project canape
 */

namespace skewer\components\config\installer\system_action\uninstall;

use skewer\components\config\installer;
use skewer\components\config\InstallPrototype;

class ExecuteModuleInstructions extends installer\Action
{
    /**
     * Экземпляр класса установки модуля.
     *
     * @var null|InstallPrototype
     */
    protected $installer;

    public function init()
    {
        $installer = $this->module->installClass;

        /* @var InstallPrototype $moduleInstaller */
        $this->installer = new $installer($this->module->moduleConfig);
    }

    public function execute()
    {
        if ($this->installer->init()) {
            $this->installer->uninstall();
        }
    }

    public function rollback()
    {
    }
}
