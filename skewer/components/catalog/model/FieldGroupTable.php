<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm\TablePrototype;

/**
 * Группы полей сущности
 * Class FieldGroupTable.
 */
class FieldGroupTable extends TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'c_field_group';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear()
            ->setNamespace(__NAMESPACE__)
            ->addField('name', 'varchar(255)', 'Системное имя')
            ->addField('title', 'varchar(255)', 'Название')
            ->addField('position', 'int(11)', 'position')
            ->addField('group_type', 'int(1)', 'group_type')
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        return new FieldGroupRow($aData);
    }

    /**
     * Сортирует группы карточек.
     *
     * @param int $iItemId id перемещаемого объекта
     * @param int $iTargetId id объекта, относительно которого идет перемещение
     * @param string $sOrderType направление переноса
     * @param string $sSortField колонка для сортировки
     *
     * @return bool
     */
    public static function sortGroups($iItemId, $iTargetId, $sOrderType = 'after', $sSortField = 'position')
    {
        if ((!$Obj = self::find($iItemId)) or // перемещаемый объект
             (!$TargetObj = self::find($iTargetId))) { // "относительный" объект
            return false;
        }

        // выбираем направление сдвига
        if ($Obj->{$sSortField} < $TargetObj->{$sSortField}) {
            $sSign = '-1';
            $iNewPos = ($sOrderType == 'after') ? $TargetObj->{$sSortField} : $TargetObj->{$sSortField} - 1;
            $iStartPos = $Obj->{$sSortField};
            $iEndPos = ($sOrderType == 'after') ? $TargetObj->{$sSortField} + 1 : $TargetObj->{$sSortField};
        } else {
            $sSign = '1';
            $iNewPos = ($sOrderType == 'after') ? $TargetObj->{$sSortField} + 1 : $TargetObj->{$sSortField};
            $iStartPos = ($sOrderType == 'after') ? $TargetObj->{$sSortField} : $TargetObj->{$sSortField} - 1;
            $iEndPos = $Obj->{$sSortField};
        }

        \Yii::$app->db->createCommand('
            UPDATE `' . self::$sTableName . "`
            SET `{$sSortField}` = `{$sSortField}` + {$sSign}
            WHERE
                `{$sSortField}` > {$iStartPos} AND
                `{$sSortField}` < {$iEndPos}
        ")->execute();

        \Yii::$app->db->createCommand('
            UPDATE `' . self::$sTableName . "`
            SET `{$sSortField}` = {$iNewPos}
            WHERE `id` = " . $Obj->id . '
        ')->execute();

        return true;
    }
}
