<?php

namespace tests\codeception\fixtures\base;

use skewer\components\catalog\model\EntityRow;
use skewer\components\catalog\model\EntityTable;
use tests\codeception\fixtures\ActiveFixturePrototype;

class CEntityFixture extends ActiveFixturePrototype
{
    public $tableName = 'c_entity';

    public $dataFile = 'tests/codeception/fixtures/base/data/c_entity.php';

    public $depends = [
        '\tests\codeception\fixtures\base\CFieldAttrFixture',
        '\tests\codeception\fixtures\base\CFieldFixture',
        '\tests\codeception\fixtures\base\CFieldGroupFixture',
        '\tests\codeception\fixtures\base\CValidatorFixture',
    ];

    public function load()
    {
        if (\Yii::$app->db->getTableSchema($this->tableName, true) === null) {
            EntityTable::rebuildTable();
            \Yii::$app->db->schema->refresh();
        }

        parent::load();

        /** @var EntityRow[] $aEntities */
        $aEntities = EntityTable::find()->getAll();

        foreach ($aEntities as $oEntity) {
            $oEntity->updCache();
        }

        \Yii::$app->db->schema->refresh();
    }
}
