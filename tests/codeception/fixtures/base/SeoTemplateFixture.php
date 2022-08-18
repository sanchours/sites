<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class SeoTemplateFixture extends ActiveFixturePrototype
{
    public $tableName = 'seo_templates';

    public $dataFile = 'tests/codeception/fixtures/base/data/seo_templates.php';
}
