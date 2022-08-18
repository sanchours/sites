<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class SearchIndexFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\search\models\SearchIndex';

    public $dataFile = 'tests/codeception/fixtures/base/data/search_index.php';
}
