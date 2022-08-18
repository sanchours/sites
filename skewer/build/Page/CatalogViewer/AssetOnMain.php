<?php

namespace skewer\build\Page\CatalogViewer;

use yii\web\AssetBundle;
use yii\web\View;

class AssetOnMain extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/CatalogViewer/web/';

    public $css = [
        'css/owl-carousel-catalogtheme.css',
        'css/owl-carousel-relatedtheme.css',
    ];

    public $js = [
        'js/fotorama.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\build\Page\CatalogViewer\Asset',
        'skewer\libs\owlcarousel\Asset',
        'skewer\ext\fotorama\Asset',
    ];
}
