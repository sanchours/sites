<?php

namespace skewer\build\Tool\Regions\view;

use skewer\components\ext\view\FormView;

class EditValueLabel extends FormView
{
    public $label;
    public $title;

    public function build()
    {
        $this->_module->setPanelName($this->title);

        $this->_form
            ->field('id', 'ID', 'hide', [])
            ->field('regionId', 'ID', 'hide', [])
            ->field('title', \Yii::t('labels', 'title'), 'show', [])
            ->field('alias', \Yii::t('labels', 'alias'), 'show', [])
            ->field('default', \Yii::t('regions', 'value'), 'wyswyg', ['listColumns' => ['flex' => 5]])
            ->buttonSave('SaveValueLabel')
            ->buttonCancel('ListLabels')
            ->setValue($this->label);
    }
}
