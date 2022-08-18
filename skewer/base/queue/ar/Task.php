<?php

namespace skewer\base\queue\ar;

use skewer\base\ft;
use skewer\base\orm\TablePrototype;

class Task extends TablePrototype
{
    protected static $sTableName = 'task';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('global_id', 'int(11)', 'global_id')
            ->addField('title', 'varchar(255)', 'title')
            ->addField('class', 'varchar(128)', 'class')
            ->addField('parameters', 'text', 'parameters')
            ->addField('priority', 'int(3)', 'status')
            ->addField('resource_use', 'int(3)', 'resource_use')
            ->addField('target_area', 'int(3)', 'target_area')
            ->addField('upd_time', 'datetime', 'upd_time')
            ->setDefaultVal('now')
            ->addField('mutex', 'int(1)', 'mutex')
            ->addField('status', 'int(5)', 'status')
            ->addField('parent', 'int(11)', 'parent')
            ->addField('md5', 'varchar(64)', 'md5')
            ->save()
            //->build()
;
    }

    /**
     * @param array $aData
     *
     * @return TaskRow
     */
    public static function getNewRow($aData = [])
    {
        $oRow = new TaskRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
