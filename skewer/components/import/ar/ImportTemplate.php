<?php

namespace skewer\components\import\ar;

use skewer\base\ft;
use skewer\base\orm;

class ImportTemplate extends orm\TablePrototype
{
    protected static $sTableName = 'import_template';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('title', 'varchar(255)', 'title')
            ->addField('card', 'varchar(255)', 'card')
            ->addField('coding', 'varchar(25)', 'coding')
            ->addField('type', 'int(11)', 'type')
            ->addField('source', 'varchar(512)', 'source')
            ->addField('provider_type', 'int(11)', 'provider_type')
            ->addField('settings', 'text', 'settings')
            ->addField('use_dict_cache', 'int(11)', 'use_dict_cache')
            ->addField('use_goods_hash', 'int(11)', 'use_goods_hash')
            ->addField('send_error', 'int(1)', 'send_error')
            ->addField('clear_log', 'int(1)', 'send_error')
            ->addField('send_notify', 'int(1)', 'send_notify')

            ->addColumnSet('required', ['title', 'card', 'provider_type', 'type'])

            ->save()
//            ->build()
;
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new ImportTemplateRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
