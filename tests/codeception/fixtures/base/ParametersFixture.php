<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class ParametersFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\base\section\models\ParamsAr';

    public $dataFile = 'tests/codeception/fixtures/base/data/parameters.php';
}
