<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class FilterSettings4CardFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\build\Catalog\Filters\model\FilterSettings4Card';

    public $dataFile = 'tests/codeception/fixtures/base/data/filterSettings4Card.php';
}
