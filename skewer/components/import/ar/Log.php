<?php

namespace skewer\components\import\ar;

use skewer\base\ft;
use skewer\base\orm;

class Log extends orm\TablePrototype
{
    protected static $sTableName = 'import_logs';
    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('tpl', 'int(11)', 'tpl')
            ->addField('task', 'int(11)', 'task')
            ->addField('name', 'varchar(255)', 'name')
            ->addField('value', 'text', 'value')
            ->addField('list', 'int(1)', 'list')
            ->addField('saved', 'int(1)', 'saved')
            ->save()
            //->build()
;
    }

    /**
     * @param array $aData
     *
     * @return LogRow
     */
    public static function getNewRow($aData = [])
    {
        $oRow = new LogRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    /**
     * Вернёт список основных параметров лога задачи.
     *
     * @param int $iTaskId - id задачи
     *
     * @return array
     */
    public static function getNonListParams($iTaskId)
    {
        $aParams = Log::find()
            ->fields(['id', 'name', 'list', 'value'])
            ->where('task', $iTaskId)
            ->andWhere('list', '0')
            ->asArray()->getAll();

        return $aParams;
    }

    /**
     * Вернёт список параметров(list=1) лога задачи.
     *
     * @param int $iTaskId - id задачи
     * @param int $iOnPage - количество на страницу
     * @param int $iPageNum - номер страницы
     * @param int $iCount - общее количество параметров
     *
     * @return array
     */
    public static function getListParams($iTaskId, $iOnPage, $iPageNum, &$iCount = 0)
    {
        $oQuery = Log::find()
            ->fields(['id', 'name', 'list', 'value'])
            ->where('task', $iTaskId)
            ->andWhere('list', '1')
            ->limit($iOnPage, $iOnPage * $iPageNum)
            ->order('id')
            ->asArray();

        $aListParams = $oQuery->setCounterRef($iCount)->getAll();

        return $aListParams;
    }
}
