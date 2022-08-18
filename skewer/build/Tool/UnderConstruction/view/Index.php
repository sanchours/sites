<?php

namespace skewer\build\Tool\UnderConstruction\view;

use skewer\base\ft\Editor;
use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\view\FormView;

class Index extends FormView
{
    public $aIndex;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if (CurrentAdmin::isSystemMode()) {
            $this->_form
                ->fieldCheck('show', \Yii::t('uconst', 'field_stub'))
                ->field('template', \Yii::t('uconst', 'field_template'), Editor::WYSWYG)
                ->buttonSave('save', \Yii::t('uconst', 'save'))
                ->setValue($this->aIndex);
        }
    }
}
