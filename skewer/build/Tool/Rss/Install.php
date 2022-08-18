<?php

namespace skewer\build\Tool\Rss;

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

    // func

    public function install()
    {
        return true;
    }

    // func

    public function uninstall()
    {
        $this->executeSQLQuery("DELETE FROM `sys_vars` WHERE `sv_name` = 'Rss.image'");
        $this->executeSQLQuery("DELETE FROM `sys_vars` WHERE `sv_name` = 'Rss.sections'");

        @unlink(Api::getDirRss() . Api::FILENAME_RSS);

        return true;
    }

    // func
}// class
