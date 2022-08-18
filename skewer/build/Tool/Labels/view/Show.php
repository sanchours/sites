<?php

namespace skewer\build\Tool\Labels\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $label;
    public $title;

    public function build()
    {
        $this->_module->setPanelName($this->title);

        $this->_form
            ->field('id', 'ID', 'hide', [])
            ->field('title', \Yii::t('labels', 'title'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('alias', \Yii::t('labels', 'alias'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('default', \Yii::t('labels', 'default'), 'wyswyg', ['listColumns' => ['flex' => 5]])
            ->buttonSave()
            ->buttonCancel()
            ->setValue($this->label);
    }
}
