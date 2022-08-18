<?php

namespace skewer\build\Design\Frame;

use yii\web\AssetBundle;
use yii\web\View;

class JeegoocontextAsset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Design/Frame/web/';
    public $css = [
    ];
    public $js = [
        'js/jquery.jeegoocontext.min.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
