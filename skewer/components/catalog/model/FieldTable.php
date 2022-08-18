<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm\ActiveRecord;
use skewer\base\orm\state\StateSelect;
use skewer\base\orm\TablePrototype;

/**
 * Поля сущности
 * Class FieldTable.
 *
 * @method static FieldRow findOne($where)
 * @method static bool|ActiveRecord|StateSelect|FieldRow find( $id = null )
 */
class FieldTable extends TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'c_field';

    protected static function initModel()
    {
        ft\Entity::get('c_field')
            ->clear()
            ->setNamespace(__NAMESPACE__)
            ->addField('name', 'varchar(64)', 'Системное имя')
            ->addValidator('unique', ['fields' => ['name', 'entity']])
            ->addValidator('systemName')
            ->addValidator('set')
            ->addField('title', 'varchar(255)', 'Название')
            ->addValidator('set')
            ->addField('entity', 'int', 'Сущность')
            ->addField('type', 'varchar(32)', 'Тип данных')
            ->addField('link_type', 'varchar(4)', 'Тип связи')
            ->addField('link_id', 'int', 'Модели источника данных')
            ->addField('size', 'int', 'Размер в базе')
            ->addField('group', 'int', 'Группа полей')
            ->addField('editor', 'varchar(32)', 'Тип редактора')
            ->addField('widget', 'varchar(255)', 'Визуализатор')
            ->addField('modificator', 'varchar(255)', 'Преобразователь')
            ->addField('validator', 'varchar(255)', 'Валидатор')
            ->addField('def_value', 'varchar(255)', 'Значение по-умолчанию')
            ->addField('position', 'int', 'Положение')
            ->addField('prohib_del', 'int', 'Запрет на удаление')
            ->addField('no_edit', 'int', 'Запрет на редактирование')
            ->addDefaultProcessorSet()
            ->save();
    }

    public static function getNewRow($aData = [])
    {
        return new FieldRow($aData);
    }

    public static function sort($aDragItem, $aDropItem, $sPos = 'after')
    {
        $sSortField = 'position';

        // поля должны быть одной судности и в одной группе
        if (($aDragItem['entity'] != $aDropItem['entity']) || ($aDragItem['group'] != $aDropItem['group'])) {
            return false;
        }

        // актуализация данных
        $oDragItem = self::find($aDragItem['id']);
        $oDropItem = self::find($aDropItem['id']);
        $aDragItem[$sSortField] = $oDragItem->{$sSortField};
        $aDropItem[$sSortField] = $oDropItem->{$sSortField};

        // выбираем напрвление сдвига
        if ($aDragItem[$sSortField] > $aDropItem[$sSortField]) {
            $iStartPos = $aDropItem[$sSortField];
            if ($sPos == 'before') {
                --$iStartPos;
            }
            $iEndPos = $aDragItem[$sSortField];
            $iNewPos = $sPos == 'before' ? $aDropItem[$sSortField] : $aDropItem[$sSortField] + 1;
            self::shiftPos($aDragItem['entity'], $iStartPos, $iEndPos, '+');
            self::changePos($aDragItem['id'], $iNewPos);
        } else {
            $iStartPos = $aDragItem[$sSortField];
            $iEndPos = $aDropItem[$sSortField];
            if ($sPos == 'after') {
                ++$iEndPos;
            }
            $iNewPos = $sPos == 'after' ? $aDropItem[$sSortField] : $aDropItem[$sSortField] - 1;
            self::shiftPos($aDragItem['entity'], $iStartPos, $iEndPos, '-');
            self::changePos($aDragItem['id'], $iNewPos);
        }

        return true;
    }

    /**
     * Сдвиг позиций полей.
     *
     * @param int $iEntityId ид сущности
     * @param int $iStartPos ид стартовой позиции
     * @param int $iEndPos ид конечной позиции
     * @param string $sSign направление сдвига
     *
     * @return bool
     */
    private static function shiftPos($iEntityId, $iStartPos, $iEndPos, $sSign = '+')
    {
        if (!in_array($sSign, ['-', '+'])) {
            $sSign = '+';
        }

        self::update()
            ->set("position=position{$sSign}?", 1)
            ->where('entity', $iEntityId)
            ->where('position>?', $iStartPos)
            ->where('position<?', $iEndPos)
            ->get();

        return true;
    }

    /**
     * Изменение позиции поля.
     *
     * @param int $iFieldId ид поля
     * @param int $iPos ид позиции
     *
     * @return bool
     */
    private static function changePos($iFieldId, $iPos)
    {
        /** @var FieldRow $oField */
        $oField = self::find($iFieldId);

        if (empty($oField)) {
            return false;
        }

        $oField->position = $iPos;

        $oField->save();

        return true;
    }

    /**
     * Выборка полей.
     *
     * @param int $id
     * @param array $aField необходимые поля
     * @param array $aWhere массив условий вида ['name_field'=>'value']
     *
     * @return array
     */
    public static function getFieldById($id, $aField = [], $aWhere = [])
    {
        $oFieldTable = self::find()->where('entity', $id);
        if ($aWhere) {
            foreach ($aWhere as $sKey => $sValue) {
                $oFieldTable->where($sKey, $sValue);
            }
        }
        if ($aField) {
            $oFieldTable->fields($aField);
        }
        $aFieldTable = $oFieldTable->asArray()->getAll();

        return $aFieldTable;
    }
}
