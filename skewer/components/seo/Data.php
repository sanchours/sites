<?php

namespace skewer\components\seo;

use skewer\base\ft;
use skewer\base\orm;

class Data extends orm\TablePrototype
{
    protected static $sTableName = 'seo_data';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear()
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('group', 'varchar(16)', 'group_data')
            ->addField('row_id', 'int(11)', 'row_id')
            ->addField('section_id', 'int(11)', 'section_id')
            ->addField('title', 'text', 'meta_title')
            ->addField('keywords', 'text', 'meta_keywords')
            ->addField('description', 'text', 'meta_description')
            ->addField('seo_gallery', 'int(11)', 'openGraph_photo')
            ->addField('frequency', 'varchar(16)', 'frequency')
            ->addField('priority', 'float', 'priority')
            ->addField('none_index', 'int(1)', 'none_index')
            ->addField('none_search', 'int(1)', 'none_search')
            ->addField('add_meta', 'text', 'add_meta')
            ->save()
            //->build()
;
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new DataRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }
}
