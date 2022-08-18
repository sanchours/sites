<?php

namespace skewer\base\ui;

class Api
{
    /**
     * Сортирует объекты списка.
     *
     * @param int $iItemId id перемещаемого объекта
     * @param int $iItemTargetId id объекта, относительно которого идет перемещение
     * @param string $ARClass Класс ActiveRecord таблицы объектов
     * @param string $sPosition направление переноса
     * @param string $sFieldGroup Название поля, внутри группы которого будет осущетсвлена сортировка
     * @param string $sFieldId Название поля идентификатора строки в таблице
     * @param string $sSortField колонка для сортировки
     *
     * @return bool
     */
    public static function sortObjects($iItemId, $iItemTargetId, $ARClass, $sPosition = 'before', $sFieldGroup = '', $sFieldId = 'id', $sSortField = 'priority')
    {
        /** @var \yii\db\ActiveRecord $ARClass */
        if ((!$oObj = $ARClass::findOne($iItemId)) or
             (!$oObjTarget = $ARClass::findOne($iItemTargetId)) or
             ($sFieldGroup and $oObj[$sFieldGroup] != $oObjTarget[$sFieldGroup])) {
            return false;
        }

        // Выбираем направление сдвига
        if ($oObj[$sSortField] < $oObjTarget[$sSortField]) {
            $sSign = '-1';
            $iNewPos = ($sPosition == 'after') ? $oObjTarget[$sSortField] : $oObjTarget[$sSortField] - 1;
            $iStartPos = $oObj[$sSortField];
            $iEndPos = ($sPosition == 'after') ? $oObjTarget[$sSortField] + 1 : $oObjTarget[$sSortField];
        } else {
            $sSign = '1';
            $iNewPos = ($sPosition == 'after') ? $oObjTarget[$sSortField] + 1 : $oObjTarget[$sSortField];
            $iStartPos = ($sPosition == 'after') ? $oObjTarget[$sSortField] : $oObjTarget[$sSortField] - 1;
            $iEndPos = $oObj[$sSortField];
        }

        $ARClass::updateAllCounters([$sSortField => $sSign], [
            'AND',
            ($sFieldGroup ? [$sFieldGroup => $oObj[$sFieldGroup]] : 1),
            ['>', $sSortField, $iStartPos],
            ['<', $sSortField, $iEndPos],
        ]);
        $ARClass::updateAll([$sSortField => $iNewPos], [$sFieldId => $oObj[$sFieldId]]);

        return true;
    }
}
