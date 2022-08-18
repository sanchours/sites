<?php

namespace skewer\components\config\installer;

use skewer\base\command;

class InstallModule extends command\Hub
{
    protected $aCommandList = [];

    public function __construct(Module $module)
    {
        $this->addCommandList([
            new system_action\install\RegisterConfig($module),
            new system_action\install\RegisterLanguage($module),
            new system_action\install\ExecuteModuleInstructions($module),
            new system_action\install\RegisterCss($module),
        ]);
    }
}
