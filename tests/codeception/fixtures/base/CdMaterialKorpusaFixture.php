<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CdMaterialKorpusaFixture extends ActiveFixturePrototype
{
    public $tableName = 'cd_material_korpusa';

    public $dataFile = 'tests/codeception/fixtures/base/data/cd_material_korpusa.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
