<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm\TablePrototype;

/**
 * Атрибуты полей сущности
 * Class FieldAttrTable.
 */
class FieldAttrTable extends TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'c_field_attr';

    protected static function initModel()
    {
        ft\Entity::get('c_field_attr')
            ->clear()
            ->setNamespace(__NAMESPACE__)
            ->addField('field', 'int', 'Поле')
            ->addField('tpl', 'varchar(255)', 'Шаблон')
            ->addField('value', 'varchar(255)', 'Название')
            ->addDefaultProcessorSet()
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        return new FieldAttrRow($aData);
    }
}
