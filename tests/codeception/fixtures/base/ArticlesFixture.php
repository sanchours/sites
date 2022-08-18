<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\skActiveFixture;

class ArticlesFixture extends skActiveFixture
{
    public $tableName = 'articles';

    public $dataFile = 'tests/codeception/fixtures/base/data/articles.php';

    public $ArClass = '\skewer\build\Page\Articles\Model\Articles';
}
