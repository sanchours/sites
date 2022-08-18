<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CFieldFixture extends ActiveFixturePrototype
{
    public $tableName = 'c_field';

    public $dataFile = 'tests/codeception/fixtures/base/data/c_field.php';
}
