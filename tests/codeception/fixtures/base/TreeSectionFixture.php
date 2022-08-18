<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class TreeSectionFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\base\section\models\TreeSection';

    public $dataFile = 'tests/codeception/fixtures/base/data/tree_section.php';
}
