<?php

namespace skewer\build\Tool\ServiceSections\view;

use skewer\components\ext\view\FormView;

class FormSection extends FormView
{
    public $item;

    public $languages;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide')
            ->fieldString('name', \Yii::t('languages', 'section_name'), ['listColumns' => ['flex' => 3]])
            ->fieldString('title', \Yii::t('languages', 'section_title'), ['listColumns' => ['flex' => 5]])
            ->fieldInt('value', \Yii::t('languages', 'section_value'), ['listColumns' => ['flex' => 5]])
            ->fieldSelect('language', \Yii::t('languages', 'section_language'), $this->languages, ['listColumns' => ['flex' => 5]], false)
            ->buttonConfirm('save', \Yii::t('adm', 'save'), \Yii::t('languages', 'section_save_confirm'), 'icon-save', ['confirm' => true])
            ->buttonBack('list');
        $this->_form->setValue($this->item->getAttributes());
    }
}
