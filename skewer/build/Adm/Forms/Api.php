<?php

namespace skewer\build\Adm\Forms;

use skewer\base\orm\Query;
use skewer\components\targets\CheckTarget;

class Api
{
    public static function checkTarget(CheckTarget $target)
    {
        $aForms = Query::SelectFrom('form')
            ->where('target_yandex', $target->sName)
            ->orWhere('target_google', $target->sName)
            ->asArray()
            ->getAll();

        foreach ($aForms as $form) {
            $target->addCheckTarget(
                \Yii::t('Forms', 'Forms.Adm.tab_name') . ' : ' . $form['form_title']
            );
        }
    }

    public static function className()
    {
        return get_called_class();
    }
}
