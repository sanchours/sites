<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CdBrendyFixture extends ActiveFixturePrototype
{
    public $tableName = 'cd_brendy';

    public $dataFile = 'tests/codeception/fixtures/base/data/cd_brendy.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
