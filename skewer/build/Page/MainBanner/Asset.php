<?php

namespace skewer\build\Page\MainBanner;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/MainBanner/web/';

    public $css = [
        'css/fotorama-mainbanner-theme.css',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $js = [
        'js/initBanner.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'skewer\ext\fotorama\Asset',
    ];
}
