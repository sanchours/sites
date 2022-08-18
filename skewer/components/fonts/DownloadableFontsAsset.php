<?php

namespace skewer\components\fonts;

use yii\web\AssetBundle;

class DownloadableFontsAsset extends AssetBundle
{
    public $css = [];

    public function init()
    {
        $this->sourcePath = Api::getDirPathDownloadedFonts();

        $aFonts = Api::getListFonts(true, Api::TYPE_FONT_EXTERNAL);

        foreach ($aFonts as $aFont) {
            $sDir = Api::getDirPathDownloadedFonts() . $aFont['path'];

            if (!is_dir($sDir)) {
                continue;
            }

            if (!file_exists($sDir . '/stylesheet.css')) {
                continue;
            }

            $this->css[] = $aFont['path'] . '/stylesheet.css';
        }

        parent::init();
    }
}
