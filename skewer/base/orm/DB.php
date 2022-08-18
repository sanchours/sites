<?php

namespace skewer\base\orm;

/**
 * Api для работы на уровне базы данных
 * Class DB.
 */
class DB
{
    /**
     * Оптимизация таблиц БД.
     *
     * @throws \yii\db\Exception
     */
    public static function optimizeTables()
    {
        // Оптимизация таблиц типа MyISAM делает так же дефрагментацию
        $oQuery = \Yii::$app->db->createCommand("SHOW TABLE STATUS WHERE engine IN ('MyISAM')")->query();
        while ($sTableName = $oQuery->readColumn(0)) {
            \Yii::$app->db->createCommand("OPTIMIZE TABLE `{$sTableName}`")->execute();
        }

        // Таблицы innoDB не нуждаются в оптимизации. Ниже делается только дефрагментация
        $oQuery = \Yii::$app->db->createCommand("SHOW TABLE STATUS WHERE engine IN ('innoDB')")->query();
        while ($sTableName = $oQuery->readColumn(0)) {
            \Yii::$app->db->createCommand("ALTER TABLE `{$sTableName}` ENGINE = InnoDB")->execute();
        }
    }
}
