<?php

namespace skewer\build\Page\Main\templates\footer\foot_blue;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/footer/foot_blue/web/';

    public $css = [
        'css/foot_blue.css',
        'css/foot_blue_media.css',
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
