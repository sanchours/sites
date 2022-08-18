<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm\TablePrototype;

class ValidatorTable extends TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'c_validator';

    protected static function initModel()
    {
        ft\Entity::get('c_validator')
            ->clear()
            ->setNamespace(__NAMESPACE__)
            ->addField('field', 'int(11)', 'поле')
            ->addField('name', 'varchar(255)', 'Системное имя')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        return new ValidatorRow($aData);
    }
}
