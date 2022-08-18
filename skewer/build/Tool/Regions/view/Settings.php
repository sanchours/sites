<?php

namespace skewer\build\Tool\Regions\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $title;
    public $settings;

    public function build()
    {
        $this->_module->setPanelName($this->title);

        $this->_form
            ->fieldCheck('active', \Yii::t('regions', 'active_module'))
            ->buttonSave('SaveSettings')
            ->buttonCancel()
            ->setValue($this->settings);
    }
}
