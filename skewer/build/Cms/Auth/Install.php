<?php

namespace skewer\build\Cms\Auth;

use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
