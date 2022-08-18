<?php

namespace skewer\build\Tool\ReachGoal;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\components\config\InstallPrototype;
use skewer\components\reach_goal\Target;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        $oQuery = new Target();
        $oModel = $oQuery->getModel();

        $oEntity = ft\Entity::get('reach_goal_target');
        $oEntity->setModel($oModel);
        $oEntity->build();

        return true;
    }

    // func

    public function uninstall()
    {
        Query::SQL('DROP TABLE `reach_goal_target`');
    }

    // func
}//class
