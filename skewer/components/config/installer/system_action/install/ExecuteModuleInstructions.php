<?php
/**
 * @class CheckConsistency
 *
 * @author ArmiT
 * @date 24.01.14
 * @project canape
 */

namespace skewer\components\config\installer\system_action\install;

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

    public static $aCommandsAfterInstall = [];

    public function init()
    {
        $installer = $this->module->installClass;

        /* @var InstallPrototype $moduleInstaller */
        $this->installer = new $installer($this->module->moduleConfig);
    }

    public function execute()
    {
        if ($this->installer->init()) {
            $this->installer->install();

            $aAfterInstall = $this->installer->getCommandsAfterInstall();
            foreach ($aAfterInstall as $item) {
                self::$aCommandsAfterInstall[] = $item;
            }
        }
    }

    public function rollback()
    {
        if ($this->installer->init()) {
            $this->installer->uninstall();
        }
    }
}
