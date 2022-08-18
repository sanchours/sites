<?php

namespace skewer\build\Tool\FormOrders;

use skewer\components\forms\entities\FormOrderEntity;

class Api
{
    /**
     * Виджет на вывод статуса в списке.
     *
     * @param $oItem
     * @param $sField
     *
     * @return mixed
     */
    public static function getWidget4Status($oItem, $sField)
    {
        $aStatusList = FormOrderEntity::getStatusList();

        $sVal = $oItem[$sField] ?? $sField;

        return $aStatusList[$sVal] ?? $sVal;
    }

    public static function getFormIdsForSection($aForms)
    {
        $aOut = [];

        foreach ($aForms as $form) {
            $aOut[] = $form->form_id;
        }

        return $aOut;
    }
}
