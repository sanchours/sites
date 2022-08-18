<?php

namespace skewer\build\Design\Frame;

use yii\web\AssetBundle;
use yii\web\View;

class AssetDesign extends AssetBundle
{
    public $sourcePath = '@skewer/build/Design/Frame/web/';
    public $css = [
        'css/design/design.css',
    ];
    public $js = [
        'js/design/design.js',
        'js/design/designObj.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'skewer\ext\jqueryui\Asset',
        'skewer\build\Design\Frame\JeegoocontextAsset',
    ];
}
