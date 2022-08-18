<?php

namespace skewer\build\Page\MiniCart;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/MiniCart/web/';

    public $css = [
        'css/minicart.css',
    ];

    public $js = [
        'js/mini_cart.js',
    ];

    public $depends = [
        'skewer\build\Page\Forms\Asset',
    ];

    public function init()
    {
        parent::init();
    }

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
