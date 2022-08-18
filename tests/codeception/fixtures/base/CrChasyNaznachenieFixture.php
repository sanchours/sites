<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CrChasyNaznachenieFixture extends ActiveFixturePrototype
{
    public $tableName = 'cr_chasy__naznachenie';

    public $dataFile = 'tests/codeception/fixtures/base/data/cr_chasy__naznachenie.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
