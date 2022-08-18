<?php

use skewer\components\config\PatchPrototype;
use skewer\base\section\Parameters;
use skewer\libs\Compress\ChangeAssets;
use skewer\base\SysVar;

class Patch96416 extends PatchPrototype
{

    public $sDescription = 'Слитие "compression" и "compressionHtml" и вынос в sys_vars';

    public $bUpdateCache = false;

    /**
     * @return bool|void
     */
    public function execute()
    {
        $compression = Parameters::getValByName(Yii::$app->sections->root(), '.', ChangeAssets::NAMEPARAM, true);
        Parameters::removeByName(ChangeAssets::NAMEPARAM, '.', Yii::$app->sections->root());
        Parameters::removeByName('compressionHtml', '.', Yii::$app->sections->root());

        SysVar::set(ChangeAssets::NAMEPARAM, $compression);
    }

}
