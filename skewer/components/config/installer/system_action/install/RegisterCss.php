<?php

namespace skewer\components\config\installer\system_action\install;

use skewer\components\config\installer;
use skewer\components\design\CssParser;
use yii\web\AssetBundle;

class RegisterCss extends installer\Action
{
    public function init()
    {
    }

    public function execute()
    {
        if (!class_exists($this->module->assetClass)) {
            return;
        }

        /** @var AssetBundle $oAsset */
        $oAsset = new $this->module->assetClass();

        $aCss = $oAsset->css;

        if ($aCss) {
            foreach ($aCss as $sCssFile) {
                $oCSSParser = new CssParser();

                $oCSSParser->analyzeFile($this->module->moduleRootDir . "web/{$sCssFile}");

                $oCSSParser->updateDesignSettings();
            }
        }
    }

    public function rollback()
    {
    }
}
