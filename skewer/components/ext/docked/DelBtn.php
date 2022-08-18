<?php

namespace skewer\components\ext\docked;

/**
 * Кнопка удаления.
 */
class DelBtn extends Prototype
{
    /**
     * Кнопка удаления.
     *
     * @return AddBtn
     */
    public static function create()
    {
        $oDocked = new DelBtn();
        $oDocked->setTitle(\Yii::t('adm', 'del'));
        $oDocked->setAction('delete');
        $oDocked->setState('delete');
        $oDocked->setIconCls(Api::iconDel);

        return $oDocked;
    }
}
