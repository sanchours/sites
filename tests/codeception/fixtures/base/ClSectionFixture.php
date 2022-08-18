<?php

namespace tests\codeception\fixtures\base;

use skewer\components\catalog\model\SectionTable;
use tests\codeception\fixtures\ActiveFixturePrototype;

class ClSectionFixture extends ActiveFixturePrototype
{
    public $tableName = 'cl_section';

    public $dataFile = 'tests/codeception/fixtures/base/data/cl_section.php';

    public function load()
    {
        if (\Yii::$app->db->getTableSchema($this->tableName, true) === null) {
            SectionTable::rebuildTable();
            \Yii::$app->db->schema->refresh();
        }

        parent::load();
    }
}
