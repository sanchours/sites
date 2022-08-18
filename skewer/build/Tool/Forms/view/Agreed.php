<?php

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\FormView;

class Agreed extends FormView
{
    public $license;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldCheck('agree', \Yii::t('forms', 'form_agreed'))
            ->fieldString('text', \Yii::t('forms', 'field_agreed_title'))
            ->setValue($this->license)
            ->buttonSave('agreedSave')
            ->buttonCancel('Fields');
    }
}
