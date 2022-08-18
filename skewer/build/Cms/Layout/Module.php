<?php

namespace skewer\build\Cms\Layout;

use skewer\base\site_module\Context;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;
use skewer\build\Cms\Frame;

/**
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    const labelHeader = 'header';
    const labelLeft = 'left';
    const labelTabs = 'tabs';
    const labelLog = 'log';
    const labelFooter = 'footer';

    public function allowExecute()
    {
        return true;
    }

    public function execute($defAction = '')
    {
        $this->addChildProcess(new Context(self::labelHeader, 'skewer\build\Cms\Header\Module', ctModule, []));
        $this->addChildProcess(new Context(self::labelLeft, 'skewer\build\Cms\LeftPanel\Module', ctModule, []));
        $this->addChildProcess(new Context(self::labelTabs, 'skewer\build\Cms\Tabs\Module', ctModule, []));
        if (CurrentAdmin::isSystemMode()) {
            $this->addChildProcess(new Context(self::labelLog, 'skewer\build\Cms\Log\Module', ctModule, []));
        } else {
            $this->addChildProcess(new Context(self::labelFooter, 'skewer\build\Cms\Footer\Module', ctModule, []));
        }

        $this->setJSONHeader('init', [
            'dict' => $this->getDictVals(),
            'lang' => \Yii::$app->i18n->getTranslateLanguage(),
            'ckedir' => \Yii::$app->getAssetManager()->getBundle(\skewer\libs\CKEditor\AssetForReactAdmin::className())->baseUrl,
        ]);

        return psComplete;
    }

    public function getDictVals() {
        return $this->parseLangVars(Frame\Module::getLangKeys(), 'adm');
    }

    // func
}// class
