<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CeDopolnitelnyeParametryFixture extends ActiveFixturePrototype
{
    public $tableName = 'ce_dopolnitelnye_parametry';

    public $dataFile = 'tests/codeception/fixtures/base/data/ce_dopolnitelnye_parametry.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
