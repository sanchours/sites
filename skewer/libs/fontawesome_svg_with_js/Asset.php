<?php

namespace skewer\libs\fontawesome_svg_with_js;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/libs/fontawesome_svg_with_js/web/';

    public $css = [
        'css/fa-svg-with-js.css',
    ];

    public $js = [
        'js/fontawesome-all.min.js',
    ];

    public $jsOptions = [
        'defer' => true,
    ];
}
