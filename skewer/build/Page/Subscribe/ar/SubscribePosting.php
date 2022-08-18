<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\ft;
use skewer\base\orm;

class SubscribePosting extends orm\TablePrototype
{
    protected static $sTableName = 'subscribe_posting';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get('subscribe_posting')
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('list', 'varchar(255)', 'list')
            ->addField('state', 'int(11)', 'state')
            ->addField('post_date', 'datetime', 'post_date')
            ->addField('last_pos', 'int(11)', 'last_pos')
            ->addField('id_body', 'int(11)', 'id_body')
            ->addField('id_from', 'int(11)', 'id_from')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new SubscribePostingRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
