<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\ft;
use skewer\base\orm;

class SubscribeTemplate extends orm\TablePrototype
{
    protected static $sTableName = 'subscribe_templates';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get('subscribe_templates')
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('title', 'varchar(255)', 'title')
            ->addField('content', 'text', 'content')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new SubscribeTemplateRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
