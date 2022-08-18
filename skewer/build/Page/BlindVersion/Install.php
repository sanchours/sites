<?php

namespace skewer\build\Page\BlindVersion;

use skewer\base\section\Parameters;
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
        $this->addParams();

        return true;
    }

    // func

    public function uninstall()
    {
        Api::offBlindVersion();
        $this->deleteModuleParams();

        return true;
    }

    // func

    private function addParams()
    {
        Parameters::setParams(\Yii::$app->sections->tplNew(), Module::group_params_module, Parameters::groupName, 'blindVersion.param_groupTitle');
        Parameters::setParams(\Yii::$app->sections->tplNew(), Module::group_params_module, Parameters::object, Module::getNameModule());
        Parameters::setParams(\Yii::$app->sections->tplNew(), Module::group_params_module, Parameters::layout, 'content,head,left,right');
    }
}// class
