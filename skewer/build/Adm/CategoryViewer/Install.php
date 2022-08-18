<?php

namespace skewer\build\Adm\CategoryViewer;

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
        $sGroupParam = 'CategoryViewer';

        $this->addParameter(\Yii::$app->sections->tplNew(), 'objectAdm', 'CategoryViewer', '', $sGroupParam);

        return true;
    }

    // func

    public function uninstall()
    {
        $sGroupParam = 'CategoryViewer';

        $this->removeParameter(\Yii::$app->sections->tplNew(), 'objectAdm', $sGroupParam);

        return true;
    }

    // func
}//class
