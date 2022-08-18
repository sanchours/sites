<?php

namespace unit\base\orm;

use skewer\base\ft;
use skewer\base\orm\TablePrototype;

/**
 * Пример класса наследника для тестиорвания
 * Class TableName.
 */
class TableName extends TablePrototype
{
    protected static $sTableName = 'table_name';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('id', 'int(11)')
            ->addField('name', 'varchar(255)')
            ->addField('date', 'date')
            ->addField('a', 'int(11)')
            ->addField('b', 'int(11)')
            ->addField('c', 'int(11)')
            ->save();
    }

    protected static $aFieldList = [
        'id' => [],
        'name' => [],
        'date' => [],
        'a' => [],
        'b' => [],
        'c' => [],
    ];
}
