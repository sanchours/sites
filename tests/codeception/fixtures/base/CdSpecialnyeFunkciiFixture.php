<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CdSpecialnyeFunkciiFixture extends ActiveFixturePrototype
{
    public $tableName = 'cd_specialnye_funkcii';

    public $dataFile = 'tests/codeception/fixtures/base/data/cd_specialnye_funkcii.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
