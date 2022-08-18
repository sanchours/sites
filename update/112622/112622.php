<?php

use skewer\components\config\PatchPrototype;

class Patch112622 extends PatchPrototype
{
    public $sDescription = 'Добавление поля сортировки элементов для справочников';

    public function execute()
    {
        \Yii::$app->db->createCommand("
            ALTER TABLE `c_entity`
                ADD `priority_sort` int(1) DEFAULT 0
        ")->execute();
    }
}
