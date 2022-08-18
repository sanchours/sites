<?php

namespace skewer\build\Adm\Video;

use skewer\build\Adm;
use skewer\components\config\Exception;

/**
 * Модуль фиктивный. Используется для создания директории в браузере файлов.
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    public function execute($defAction = '')
    {
        throw new Exception('Module Video is not allowed to execute.');
    }
}
