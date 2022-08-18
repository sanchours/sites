<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\ft;
use skewer\base\orm;

class SubscribeMessage extends orm\TablePrototype
{
    protected static $sTableName = 'subscribe_msg';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get('subscribe_msg')
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('title', 'varchar(255)', 'title')
            ->addField('text', 'text', 'text')
            ->addField('template', 'int(11)', 'template')
            ->addField('status', 'int(11)', 'status')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new SubscribeMessageRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
