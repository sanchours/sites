<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CFieldGroupFixture extends ActiveFixturePrototype
{
    public $tableName = 'c_field_group';

    public $dataFile = 'tests/codeception/fixtures/base/data/c_field_group.php';
}
