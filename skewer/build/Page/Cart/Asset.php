<?php

namespace skewer\build\Page\Cart;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Cart/web/';

    public $css = [
        'css/cart.css',
        'css/tocart.css',
        'css/cart_media.css',
    ];

    public $js = [
        'js/cart.js',
        'js/tocart.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
