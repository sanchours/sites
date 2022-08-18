<?php

namespace skewer\libs\owlcarousel;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/libs/owlcarousel/web/';

    public $css = [
        'owlcarousel.css'
    ];

    public $js = [
        'main.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'kv4nt\owlcarousel\OwlCarouselAsset',
    ];
}
