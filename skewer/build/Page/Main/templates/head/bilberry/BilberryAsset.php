<?php

namespace skewer\build\Page\Main\templates\head\bilberry;

use yii\web\AssetBundle;

class BilberryAsset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/head/bilberry/web/';

    public $css = [
        'css/bilberry.css',
        'css/bilberry-adaptiv.css',
    ];

    public $js = [
        'js/bilberry.js',
    ];

//    public $jsOptions = [
//        'position'=>View::POS_HEAD
//    ];

//    public $depends = [
//        'skewer\build\Page\Main\PrintAsset',
//    ];
}
