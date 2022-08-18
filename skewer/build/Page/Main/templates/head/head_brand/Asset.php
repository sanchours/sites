<?php

namespace skewer\build\Page\Main\templates\head\head_brand;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/head/head_brand/web/';

    public $css = [
        'css/head_brand.css',
        'css/head_brand_media.css',
    ];

    public $js = [
        'js/head_brand.js',
    ];

//    public $jsOptions = [
//        'position'=>View::POS_HEAD
//    ];

//    public $depends = [
//        'skewer\build\Page\Main\PrintAsset',
//    ];
}
