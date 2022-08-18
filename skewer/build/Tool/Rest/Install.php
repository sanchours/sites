<?php

namespace skewer\build\Tool\Rest;

use skewer\base\SysVar;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        SysVar::set('REST.enable', 1);
        SysVar::getSafe('REST.api_key', $this->generateApiKey());

        return true;
    }

    public function uninstall()
    {
        SysVar::set('REST.enable', 0);

        return true;
    }

    /**
     * @return string
     */
    private function generateApiKey()
    {
        return hash('sha512', session_id() . time() . '#*#BaraNeKontritsya322#*#');
    }
}