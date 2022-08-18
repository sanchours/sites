<?php

namespace skewer\libs\select2;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/libs/select2/web/';

    public $css = [
        'select2.min.css',
    ];

    public $js = [
        'select2.full.min.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
    ];
}
