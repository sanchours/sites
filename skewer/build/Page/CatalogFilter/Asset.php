<?php

namespace skewer\build\Page\CatalogFilter;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/CatalogFilter/web/';

    public $js = [
        'js/filter.js',
    ];

    public $css = [
        'css/filter.css',
        'css/filter_media.css',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\build\Page\CatalogViewer\Asset',
    ];
}
