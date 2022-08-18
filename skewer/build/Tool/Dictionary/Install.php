<?php

namespace skewer\build\Tool\Dictionary;

use skewer\components\catalog\model;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        /* Сущность */
        model\EntityTable::rebuildTable();

        /* Атрибуты */
        model\FieldAttrTable::rebuildTable();

        /* Группы полей */
        model\FieldGroupTable::rebuildTable();

        /* Поля */
        model\FieldTable::rebuildTable();

        /* Валидаторы */
        model\ValidatorTable::rebuildTable();

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    // func
}
