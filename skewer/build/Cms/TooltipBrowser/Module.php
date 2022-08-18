<?php

namespace skewer\build\Cms\TooltipBrowser;

use skewer\base\site_module\Context;
use skewer\build\Cms;

class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        // подключаем модули
        $this->addChildProcess(new Context('files', 'skewer\build\Adm\Tooltip\Module', ctModule));
    }
}
