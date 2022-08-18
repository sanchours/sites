<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CdCvetaFixture extends ActiveFixturePrototype
{
    public $tableName = 'cd_cveta';

    public $dataFile = 'tests/codeception/fixtures/base/data/cd_cveta.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
