<?php

namespace skewer\build\Tool\Regions\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $region;
    public $title;
    public $isCreate;

    public function build()
    {
        $this->_module->setPanelName($this->title);

        $this->_form
            ->field('id', 'ID', 'hide')
            ->field('domain', \Yii::t('regions', 'domain'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('utm', \Yii::t('regions', 'utm'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('city', \Yii::t('regions', 'city'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('region', \Yii::t('regions', 'region'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('fed_district', \Yii::t('regions', 'fed_district'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('active', \Yii::t('regions', 'active'), 'check', ['listColumns' => ['flex' => 3]])
            ->buttonSave()
            ->buttonCancel()
            ->buttonSeparator()
            ->setValue($this->region);

        if ($this->isCreate) {
            $this->_form
                ->button('ListLabels', \Yii::t('regions', 'btn_edit_label'), 'icon-edit')
                ->buttonConfirm('setDefault', \Yii::t('regions', 'button_set_default_title'), \Yii::t('regions', 'button_set_default_text'));
        }
    }
}
