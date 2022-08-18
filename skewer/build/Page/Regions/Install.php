<?php

namespace skewer\build\Page\Regions;

use skewer\components\config\InstallPrototype;
use skewer\components\regions\ParamForRegion;
use skewer\components\seo\Robots;
use skewer\components\seo\Sitemap;

/**
 * Class Install.
 */
class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function install()
    {
        Robots::setShortPathSysVar('robots_files/' . Robots::$nameFile);

        $dir = Robots::getDirPath();

        if (!is_dir($dir)) {
            @mkdir($dir);
        }

        Sitemap::setShortPathSysVar('sitemap_files/' . Sitemap::$nameFile);

        return true;
    }

    public function uninstall()
    {
        Robots::setDefaultValue();
        Sitemap::setDefaultValue();

        $paramRegion = new ParamForRegion();
        if ($paramRegion->hasInstallParam()) {
            $paramRegion->remove();
        }

        return true;
    }
}
