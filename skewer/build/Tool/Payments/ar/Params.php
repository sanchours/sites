<?php

namespace skewer\build\Tool\Payments\ar;

use skewer\base\ft;
use skewer\base\orm;

/**
 * Class Params.
 */
class Params extends orm\TablePrototype
{
    protected static $sTableName = 'payment_parameters';

    protected static $sKeyField = 'id';

    protected static function initModel()
    {
        ft\Entity::get('payment_parameters')
            ->clear(false)
            ->setPrimaryKey(self::$sKeyField)
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('type', 'varchar(64)', 'type')
            ->addField('name', 'varchar(64)', 'name')
            ->addField('value', 'varchar(512)', 'value')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new ParamRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    /**
     * Выборка параметра по типу и названию.
     *
     * @param $sType
     * @param $sName
     * @param $bSave
     *
     * @return array|bool|orm\ActiveRecord|ParamRow
     */
    public static function getParam($sType, $sName, $bSave = false)
    {
        $oParam = self::find()
            ->where('type', $sType)
            ->where('name', $sName)
            ->getOne();

        if (!$oParam) {
            $oParam = Params::getNewRow();
            $oParam->type = $sType;
            $oParam->name = $sName;
            if ($bSave) {
                $oParam->save();
            }
        }

        return $oParam;
    }
}
