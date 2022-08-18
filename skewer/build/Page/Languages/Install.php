<?php

namespace skewer\build\Page\Languages;

use skewer\base\section\Parameters;
use skewer\build\Design\Zones\Api;
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
        $sGroup = 'LanguageSwitch';

        Parameters::setParams(\Yii::$app->sections->tplNew(), $sGroup, Parameters::object, 'Languages');
        Parameters::setParams(\Yii::$app->sections->tplNew(), $sGroup, '.title', 'Выбор языка');
        Parameters::setParams(\Yii::$app->sections->tplNew(), $sGroup, Api::layoutParamName, 'head');

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    // func
}// class
