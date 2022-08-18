<?php

namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;

class ActiveFixturePrototype extends ActiveFixture
{
    public function unload()
    {
        $tableName = $this->tableName;
        if ($tableName === null) {
            /* @var $modelClass \yii\db\ActiveRecord */
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
        }

        // Вычищаем таблицу только если она существует
        if (\Yii::$app->db->getTableSchema($tableName, true) !== null) {
            parent::unload();
        }
    }
}
