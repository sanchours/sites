<?php

namespace skewer\build\Adm\Testing\view;

use skewer\components\ext\view\ListView;

class Modules extends ListView
{
    public $modules;
    public $isLastRun;

    public function build()
    {
        $this->_list
            ->fieldString('link', \Yii::t('testing', 'title_module'), ['listColumns.flex' => 2])
            ->fieldString('percent', 'Покрытие автотестами , %', ['listColumns.flex' => 1])
            ->fieldString('passedAutotest', 'Пройденные автотесты, %', ['listColumns.flex' => 1])
            ->fieldString('checkManualTest', 'Проверенные ручные тесты, %', ['listColumns.flex' => 1])
            ->setValue($this->modules)
            ->buttonRow('runModuleTests', \Yii::t('testing', 'start'), 'icon-reinstall')
            ->button('runBlockTests', \Yii::t('testing', 'start'), 'icon-reinstall');
    }
}
