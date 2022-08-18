<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CdStilFixture extends ActiveFixturePrototype
{
    public $tableName = 'cd_stil';

    public $dataFile = 'tests/codeception/fixtures/base/data/cd_stil.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
