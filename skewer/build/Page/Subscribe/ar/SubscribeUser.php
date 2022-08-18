<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\ft;
use skewer\base\orm;

class SubscribeUser extends orm\TablePrototype
{
    protected static $sTableName = 'subscribe_users';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get('subscribe_users')
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('email', 'char(255)', 'email')
            ->addField('person', 'char(255)', 'person')
            ->addField('city', 'char(255)', 'city')
            ->addField('ticket', 'char(255)', 'ticket')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new SubscribeUserRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
