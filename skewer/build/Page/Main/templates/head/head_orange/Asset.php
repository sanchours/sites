<?php

namespace skewer\build\Page\Main\templates\head\head_orange;

use yii\web\AssetBundle;

/**
 * Class ExampleAsset.
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/head/head_orange/web/';

    public $css = [
        'css/head_orange.css',
        'css/head_orange_media.css',
    ];

    public $js = [
        'js/head_orange.js',
    ];

//    public $jsOptions = [
//        'position'=>View::POS_HEAD
//    ];

//    public $depends = [
//        'skewer\build\Page\Main\PrintAsset',
//    ];
}
