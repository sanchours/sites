<?php

namespace skewer\build\Design\Settings\view;

use skewer\components\ext\view\FormView;

class Form extends FormView
{
    public $aTplList;
    public $sCurrentTpl;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('tpl', 'Шаблон', $this->aTplList, [], false)
            ->buttonSave('changeForm')
            ->buttonCancel();

        $this->_form->setValue([
            'tpl' => $this->sCurrentTpl,
        ]);
    }
}
