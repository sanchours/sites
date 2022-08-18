<?php

namespace skewer\build\Page\Gallery;

use yii\web\AssetBundle;
use yii\web\View;

class AssetFotorama extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Gallery/web/';

    public $css = [
    ];

    public $js = [
        'js/fotorama.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\ext\fotorama\Asset',
    ];
}
