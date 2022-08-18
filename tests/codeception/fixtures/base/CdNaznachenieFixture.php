<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CdNaznachenieFixture extends ActiveFixturePrototype
{
    public $tableName = 'cd_naznachenie';

    public $dataFile = 'tests/codeception/fixtures/base/data/cd_naznachenie.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
