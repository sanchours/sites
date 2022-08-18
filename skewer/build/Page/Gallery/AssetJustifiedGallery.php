<?php

namespace skewer\build\Page\Gallery;

use yii\web\AssetBundle;
use yii\web\View;

class AssetJustifiedGallery extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Gallery/web/';

    public $js = [
        'js/gallery-tile-init.js',
    ];

    public $depends = [
        'skewer\build\Page\Gallery\Asset',
        'skewer\libs\justifiedGallery\Asset',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
