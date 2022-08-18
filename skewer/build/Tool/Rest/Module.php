<?php

namespace skewer\build\Tool\Rest;

use skewer\base\SysVar;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\build\Tool\Rest\view\Index;

class Module extends ModulePrototype
{
    protected function actionInit()
    {
        return $this->render(new Index([
            'apiKey' => SysVar::get('REST.api_key'),
        ]));
    }
}