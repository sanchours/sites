<?php

namespace skewer\build\Cms\SliderBrowser;

use skewer\base\site_module\Context;
use skewer\build\Cms;

/**
 * Модуль для отображения галереи
 * Подчиненные модули:
 * Панель с файлами из основного интерфейса
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        // подключаем модули
        $this->addChildProcess(new Context(
            'files',
            'skewer\build\Tool\Slider\Module',
            ctModule
        ));

        $this->setCmd('init');
    }
}
