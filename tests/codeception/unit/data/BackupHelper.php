<?php

namespace unit\data;

use yii\db\ActiveRecord;

/** Хелпер для бекапирования и восстановления таблиц. Используется в тестах */
class BackupHelper
{
    protected $aMapTableNameToClassNameAr = [];

    protected $aBackUpData = [];

    public function __construct($aMapTableNameToClassNameAr)
    {
        $this->aMapTableNameToClassNameAr = $aMapTableNameToClassNameAr;
    }

    /** Бекапирование таблиц */
    public function backUpTables()
    {
        foreach ($this->aMapTableNameToClassNameAr as $sTableName => $sClassName) {
            $aTableData = [];

            foreach ($sClassName::find()->asArray()->each() as $row) {
                $aTableData[] = $row;
            }

            $this->aBackUpData[$sTableName] = $aTableData;
        }
    }

    /** Восстановление данных из бекапа */
    public function restoreTables()
    {
        /**
         * @var  string
         * @var  ActiveRecord $sClassName
         */
        foreach ($this->aMapTableNameToClassNameAr as $sTableName => $sClassName) {
            $aColumns = \Yii::$app->db->getTableSchema($sTableName)->columnNames;

            $aTableData = $this->aBackUpData[$sTableName];

            $sClassName::deleteAll();

            \Yii::$app->db->createCommand()
                ->batchInsert($sTableName, $aColumns, $aTableData)
                ->execute();

            unset($this->aBackUpData[$sTableName]);
        }
    }
}
