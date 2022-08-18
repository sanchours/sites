<?php

namespace skewer\build\Page\Main\templates\head\head_watermelon;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/head/head_watermelon/web/';

    public $css = [
        'css/head_watermelon.css',
        'css/head_watermalon_media.css',
    ];

    public $js = [
        'js/head_watermelon.js',
    ];

//    public $jsOptions = [
//        'position'=>View::POS_HEAD
//    ];

//    public $depends = [
//        'skewer\build\Page\Main\PrintAsset',
//    ];
}
