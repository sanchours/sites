<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class SeoDataFixture extends ActiveFixturePrototype
{
    public $tableName = 'seo_data';

    public $dataFile = 'tests/codeception/fixtures/base/data/seo_data.php';
}
