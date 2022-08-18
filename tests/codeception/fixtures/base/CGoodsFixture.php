<?php

namespace tests\codeception\fixtures\base;

use skewer\components\catalog\model\GoodsTable;
use tests\codeception\fixtures\skActiveFixture;

class CGoodsFixture extends skActiveFixture
{
    public $tableName = 'c_goods';

    public $dataFile = 'tests/codeception/fixtures/base/data/c_goods.php';

    public function load()
    {
        if (\Yii::$app->db->getTableSchema($this->tableName, true) === null) {
            GoodsTable::rebuildTable();
            \Yii::$app->db->schema->refresh();
        }

        parent::load();
    }
}
