<?php

namespace skewer\build\Page\Forms;

use skewer\components\config\InstallPrototype;

/**
 * Class Install.
 */
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
