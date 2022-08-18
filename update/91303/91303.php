<?php

use skewer\components\config\PatchPrototype;

class Patch91303 extends PatchPrototype
{

    public $sDescription = 'Установка типа валидации Файл для полей формы Загрузка файла';

    public $bUpdateCache = false;

    public function execute()
    {
        $this->executeSQLQuery(
            "UPDATE `form_field` SET `type_of_valid` = 'File' WHERE `type` = 'File'"
        );

        $this->executeSQLQuery(
            "UPDATE `form_field` SET `type_of_valid` = 'Text' WHERE `type_of_valid` = ''"
        );
    }

}