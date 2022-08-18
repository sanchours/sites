<?php

namespace skewer\build\Adm\Testing\view;

use skewer\components\ext\view\ListView;

class TestSuite extends ListView
{
    public $testCases;
    public $name;
    public $isLastRun;

    public function build()
    {
        $this->_module->setPanelName($this->name);

        $this->_list
            ->fieldShow(
                'description',
                \Yii::t('testing', 'list_assignment'),
                's',
                ['listColumns.flex' => 1]
            )
            ->fieldShow(
                'steps',
                \Yii::t('testing', 'list_sequencing'),
                's',
                ['listColumns.flex' => 3]
            )
            ->setValue($this->testCases)
            ->button('runTestSuite', \Yii::t('testing', 'start'), 'icon-reinstall')
            ->buttonIf($this->isLastRun, \Yii::t('testing', 'last_run'), 'lastRun', 'icon-page');
    }
}
