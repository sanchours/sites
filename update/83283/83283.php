<?php

use skewer\components\config\PatchPrototype;

class Patch83283 extends PatchPrototype
{
    public $sDescription = 'Добавлена колонка parameters.updated_at';

    public $bUpdateCache = false;

    public function execute()
    {
        $this->executeSQLQuery('ALTER TABLE `parameters` ADD `updated_at` DATETIME NOT NULL AFTER `show_val`;');

        $this->executeSQLQuery("UPDATE `parameters` SET `updated_at` = '" . date('c', time()) . "' WHERE 1");
    }
}
