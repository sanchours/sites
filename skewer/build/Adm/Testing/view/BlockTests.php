<?php

namespace skewer\build\Adm\Testing\view;

use skewer\base\site\Layer;
use skewer\components\ext\view\ListView;

class BlockTests extends ListView
{
    public $testSuites;
    public $isLastRun;

    /**
     * @throws \Exception
     */
    public function build()
    {
        $this->_list
            ->fieldString('link', \Yii::t('testing', 'title_block_test'), ['listColumns.flex' => 1])
            ->fieldCheck('autotest', \Yii::t('testing', 'autotest'))
            ->fieldString('resultLastRun', 'Запуск')
            ->buttonRowCustomJs('CheckManual', Layer::ADM, 'Testing', ['tooltip' => 'Изменить состояние'])
            ->buttonRowCustomJs('RunTestSuite', Layer::ADM, 'Testing', ['tooltip' => 'Запустить'])
            ->button('runBlockTests', \Yii::t('testing', 'start'), 'icon-reinstall')
            ->setValue($this->testSuites);
    }
}
