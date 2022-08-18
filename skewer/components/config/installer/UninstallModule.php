<?php

namespace skewer\components\config\installer;

use skewer\base\command;

class UninstallModule extends command\Hub
{
    protected $aCommandList = [];

    public function __construct(Module $module)
    {
        $this->addCommandList([
            new system_action\uninstall\UnregisterConfig($module),
            new system_action\uninstall\UnregisterLanguage($module),
            new system_action\uninstall\ExecuteModuleInstructions($module),
        ]);
    }
}
