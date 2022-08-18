<?php

namespace skewer\components\fonts;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/components/fonts/web/';

    public $css = [
        'css/fonts.css',
    ];

    public $depends = [
        '\skewer\components\fonts\DownloadableFontsAsset',
    ];

    public function init()
    {
        foreach (Api::getListFonts(true, Api::TYPE_FONT_INNER) as $aFont) {
            if (!is_dir(Api::getDirPathSystemFonts() . $aFont['path'])) {
                continue;
            }

            array_unshift($this->css, 'fonts/' . $aFont['path'] . '/stylesheet.css');
        }

        parent::init();
    }
}
