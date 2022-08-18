<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class FaqFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\build\Adm\FAQ\models\Faq';

    public $dataFile = 'tests/codeception/fixtures/base/data/faq.php';
}
