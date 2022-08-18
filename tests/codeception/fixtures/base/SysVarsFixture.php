<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class SysVarsFixture extends ActiveFixturePrototype
{
    public $tableName = 'sys_vars';

    public $dataFile = 'tests/codeception/fixtures/base/data/sys_vars.php';
}
