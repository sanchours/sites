<?php

namespace skewer\build\Page\GalleryViewer;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/GalleryViewer/web/';

    public $css = [
        'css/owl-carousel-gallerytheme.css',
    ];

    public $js = [
        'js/gallery-init.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'skewer\libs\owlcarousel\Asset',
    ];
}
