<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 06.06.2016
 * Time: 9:13.
 */

namespace skewer\build\Page\Targets;

use skewer\base\site_module;

class Module extends site_module\page\ModulePrototype
{
    public function execute()
    {
        return psComplete;
    }
}
