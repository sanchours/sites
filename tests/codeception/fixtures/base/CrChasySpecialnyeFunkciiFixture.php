<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CrChasySpecialnyeFunkciiFixture extends ActiveFixturePrototype
{
    public $tableName = 'cr_chasy__specialnye_funkcii';

    public $dataFile = 'tests/codeception/fixtures/base/data/cr_chasy__specialnye_funkcii.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
