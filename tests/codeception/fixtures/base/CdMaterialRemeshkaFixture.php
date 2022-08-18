<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class CdMaterialRemeshkaFixture extends ActiveFixturePrototype
{
    public $tableName = 'cd_material_remeshka';

    public $dataFile = 'tests/codeception/fixtures/base/data/cd_material_remeshka.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CEntityFixture',
    ];
}
