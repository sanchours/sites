<?php

namespace skewer\build\Page\Gallery;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Gallery/web/';

    public $css = [
        'css/gallery-tile.css',
        'css/gallery_media.css',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
