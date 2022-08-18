<?php

namespace skewer\build\Tool\UnderConstruction;

use skewer\base\SysVar;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        return true;
    }

    // func

    public function uninstall()
    {
        SysVar::del(Api::SYSVAR_UCONST);

        return true;
    }

    // func
}//class
