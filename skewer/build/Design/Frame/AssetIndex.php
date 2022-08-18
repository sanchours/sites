<?php

namespace skewer\build\Design\Frame;

use yii\web\AssetBundle;

class AssetIndex extends AssetBundle
{
    public $sourcePath = '@skewer/build/Design/Frame/web';
    public $css = [
        'css/main.css',
    ];
    public $js = [
        'js/f/main.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
