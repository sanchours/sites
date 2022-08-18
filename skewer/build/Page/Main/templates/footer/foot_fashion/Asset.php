<?php

namespace skewer\build\Page\Main\templates\footer\foot_fashion;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/footer/foot_fashion/web/';

    public $css = [
        'css/foot_fashion.css',
        'css/foot_fashion_media.css',
    ];

    public $js = [
        // 'js/head_orange.js'
    ];

//    public $jsOptions = [
//        'position'=>View::POS_HEAD
//    ];

//    public $depends = [
//        'skewer\build\Page\Main\PrintAsset',
//    ];
}
