<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CValidatorFixture extends ActiveFixturePrototype
{
    public $tableName = 'c_validator';

    public $dataFile = 'tests/codeception/fixtures/base/data/c_validator.php';
}
