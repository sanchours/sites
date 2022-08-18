<?php

use skewer\components\config\PatchPrototype;

class Patch100791 extends PatchPrototype
{
    public $sDescription = 'Добавляем поддержку значения null для столбцов min_cost и price таблицы orders_delivery';

    public $bUpdateCache = false;

    public function execute()
    {
        if (\Yii::$app->db->schema->getTableSchema('orders_delivery') !== null) {
            \Yii::$app->db->createCommand()
                ->alterColumn('orders_delivery', 'min_cost', 'int(11) NULL')
                ->execute();
            \Yii::$app->db->createCommand()
                ->alterColumn('orders_delivery', 'price', 'int(11) NULL')
                ->execute();
        }
    }
}
