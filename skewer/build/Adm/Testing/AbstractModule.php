<?php

namespace skewer\build\Adm\Testing;

use skewer\build\Tool\LeftList\ModulePrototype;

abstract class AbstractModule extends ModulePrototype
{
    /** @var AbstractService */
    protected $service;

    abstract protected function actionInit();

    abstract protected function actionRunTestSuite();
}
