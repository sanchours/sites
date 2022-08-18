<?php

namespace skewer\build\Design\Layout;

use skewer\base\site_module\Context;
use skewer\build\Cms;

/**
 * Модуль вывода основного интерфейсного контейнера
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    public function execute($defAction = '')
    {
        $this->addChildProcess(new Context('head', 'skewer\build\Design\Header\Module', ctModule, []));
        $this->addChildProcess(new Context('tabs', 'skewer\build\Design\Tabs\Module', ctModule, []));

        return psComplete;
    }
}
