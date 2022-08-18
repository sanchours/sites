<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CFieldAttrFixture extends ActiveFixturePrototype
{
    public $tableName = 'c_field_attr';

    public $dataFile = 'tests/codeception/fixtures/base/data/c_field_attr.php';
}
