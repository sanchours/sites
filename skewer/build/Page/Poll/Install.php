<?php

namespace skewer\build\Page\Poll;

use skewer\base\section\Parameters;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    const MODULE_NAME = 'Poll';

    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        $sGroupName = 'pollLeft';

        Parameters::createParam([
            'parent' => \Yii::$app->sections->tplNew(),
            'group' => $sGroupName,
            'name' => 'Location',
            'value' => 'left',
        ])->save();

        Parameters::createParam([
            'parent' => \Yii::$app->sections->tplNew(),
            'group' => $sGroupName,
            'name' => 'layout',
            'value' => 'left',
        ])->save();

        Parameters::createParam([
            'parent' => \Yii::$app->sections->tplNew(),
            'group' => $sGroupName,
            'name' => 'object',
            'value' => 'Poll',
        ])->save();

        /** pollRight */
        $sGroupName = 'pollRight';

        Parameters::createParam([
            'parent' => \Yii::$app->sections->tplNew(),
            'group' => $sGroupName,
            'name' => 'Location',
            'value' => 'right',
        ])->save();

        Parameters::createParam([
            'parent' => \Yii::$app->sections->tplNew(),
            'group' => $sGroupName,
            'name' => 'layout',
            'value' => 'right',
        ])->save();

        Parameters::createParam([
            'parent' => \Yii::$app->sections->tplNew(),
            'group' => $sGroupName,
            'name' => 'object',
            'value' => 'Poll',
        ])->save();

        return true;
    }

    // func

    public function uninstall()
    {
        $this->deleteModuleParams();

        return true;
    }

    // func
}// class
