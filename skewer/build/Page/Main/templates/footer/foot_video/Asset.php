<?php

namespace skewer\build\Page\Main\templates\footer\foot_video;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/footer/foot_video/web/';

    public $css = [
        'css/foot_video.css',
        'css/foot_video_media.css',
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
