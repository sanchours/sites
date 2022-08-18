<?php

namespace skewer\build\Tool\AuthSocialNetwork\view;

use skewer\components\ext\view\FormView;
use skewer\libs\ulogin\Api;

class Index extends FormView
{
    public $values;

    public function build()
    {
        $this->_form
            ->fieldCheck('authSocialNetwork', \Yii::t('socialNetwork', 'authSocialNetwork'), ['onUpdateAction' => 'updFields'])
            ->fieldSelect('typeDisplay', \Yii::t('socialNetwork', 'type_view'), Api::getListTypesDisplay(), [
                'groupTitle' => \Yii::t('socialNetwork', 'view_widget'),
            ], false)
            ->fieldSelect('typeTheme', \Yii::t('socialNetwork', 'type_theme'), Api::getListTypesTheme(), [
                'groupTitle' => \Yii::t('socialNetwork', 'view_widget'), ], false)
            ->setValue($this->values)
            ->buttonSave('save');
    }
}
