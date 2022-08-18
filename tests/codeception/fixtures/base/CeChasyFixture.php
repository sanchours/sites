<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CeChasyFixture extends ActiveFixturePrototype
{
    public $tableName = 'ce_chasy';

    public $dataFile = 'tests/codeception/fixtures/base/data/ce_chasy.php';

    public $depends = [
      '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
