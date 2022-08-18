<?php

namespace tests\codeception\fixtures\base;

use skewer\components\catalog\model\SemanticTable;
use tests\codeception\fixtures\ActiveFixturePrototype;

class ClSemanticFixture extends ActiveFixturePrototype
{
    public $tableName = 'cl_semantic';

    public $dataFile = 'tests/codeception/fixtures/base/data/cl_semantic.php';

    public function load()
    {
        if (\Yii::$app->db->getTableSchema($this->tableName, true) === null) {
            SemanticTable::rebuildTable();
            \Yii::$app->db->schema->refresh();
        }

        parent::load();
    }
}
