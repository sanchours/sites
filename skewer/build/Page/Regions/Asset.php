<?php

namespace skewer\build\Page\Regions;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Regions/web/';

    public $css = [
        'css/region.css',
    ];

    public $js = [
        'js/region.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\ext\jqueryui\Asset',
    ];
}
