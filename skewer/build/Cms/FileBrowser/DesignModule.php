<?php

namespace skewer\build\Cms\FileBrowser;

use skewer\base\site_module\Context;
use skewer\build\Cms;

/**
 * Модуль для отображения раскладки фалового менеджера для дизайнерского режима
 * Подчиненные модули:
 *  Панель с файлами из основного интерфейса
 * Class DesignModule.
 */
class DesignModule extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        $this->addChildProcess(new Context('files', 'skewer\build\Adm\Files\DesignBrowserModule', ctModule, []));
    }
}
