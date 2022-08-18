<?php

namespace skewer\build\Page\AdaptiveMode;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/AdaptiveMode/web/';

    public $css = [
        'css/adaptive_mode.css',
        'css/adaptive_tablet.css',
        'css/adaptive_mobile.css',
    ];

    public $js = [
        'js/adaptive_mode.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
