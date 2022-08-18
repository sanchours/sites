<?php

namespace skewer\build\Page\Main\templates\head\head_shop;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/head/head_shop/web/';

    public $css = [
        'css/head_shop.css',
        'css/head_shop_media.css',
    ];

    public $js = [
        'js/head_shop.js',
    ];

//    public $jsOptions = [
//        'position'=>View::POS_HEAD
//    ];

//    public $depends = [
//        'skewer\build\Page\Main\PrintAsset',
//    ];
}
