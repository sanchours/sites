<?php

namespace skewer\build\Cms\Log;

use skewer\base\site\Site;
use skewer\build\Cms;

/**
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    public function execute($defAction = '')
    {
        $sHeader = sprintf(
            '%s (ver %s)',
            \Yii::t('forms', 'logPanelHeader'),
            Site::getCmsVersion()
        );

        $this->setModuleLangValues(
            [
                'logPanelHeader' => $sHeader,
                'clear' => 'clear',
                'log' => 'log',
                'err' => 'err',
            ]
        );
        $this->setData('cmd', 'init');

        return psComplete;
    }

    // func
}// class
