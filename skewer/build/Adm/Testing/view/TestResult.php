<?php

namespace skewer\build\Adm\Testing\view;

use skewer\components\ext\view\FormView;

class TestResult extends FormView
{
    public $settings;

    public function build()
    {
        $this->_module->setPanelName(\Yii::t('testing', 'result_run'));

        $this->_form
            ->fieldSpec('format', \Yii::t('testing', 'title'), 'TestResult', $this->settings)
            ->button('runTestSuite', \Yii::t('testing', 'restart'), 'icon-reinstall')
            ->buttonCancel();
    }
}
