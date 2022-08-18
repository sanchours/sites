<?php

namespace skewer\build\Design\ParamPanel;

use skewer\base\site_module\Context;
use skewer\build\Cms;

/**
 * Модуль для вывода панели с редакторм параметров
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    public function execute($defAction = '')
    {
        $this->addChildProcess(new Context('tree', 'skewer\build\Design\ParamTree\Module', ctModule, []));
        $this->addChildProcess(new Context('params', 'skewer\build\Design\ParamEditor\Module', ctModule, []));

        return psComplete;
    }
}
