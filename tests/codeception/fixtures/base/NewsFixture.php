<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class NewsFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\build\Adm\News\models\News';

    public $dataFile = 'tests/codeception/fixtures/base/data/news.php';
}
