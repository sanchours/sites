<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\skActiveFixture;

class CoBaseCardFixture extends skActiveFixture
{
    public $tableName = 'co_base_card';

    public $dataFile = 'tests/codeception/fixtures/base/data/co_base_card.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
