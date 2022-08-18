<?php

namespace skewer\components\ext\docked;

/**
 * Кнопка добаления.
 */
class AddBtn extends Prototype
{
    /**
     * Кнопка добавления.
     *
     * @return AddBtn
     */
    public static function create()
    {
        $oDocked = new AddBtn();
        $oDocked->setTitle(\Yii::t('adm', 'add'));
        $oDocked->setAction('addForm');
        $oDocked->setState('save');
        $oDocked->setIconCls(Api::iconAdd);

        return $oDocked;
    }
}
