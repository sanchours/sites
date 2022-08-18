<?php

namespace skewer\build\Page\CategoryViewer;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/CategoryViewer/web/';

    public $css = [
        'css/category-viewer.css',
    ];

    public $js = [
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
