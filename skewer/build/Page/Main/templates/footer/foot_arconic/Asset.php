<?php

namespace skewer\build\Page\Main\templates\footer\foot_arconic;

use yii\web\AssetBundle;

/**
 * #57390.
 * Class ExampleAsset.
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/footer/foot_arconic/web/';

    public $css = [
        'css/foot_arconic.css',
        'css/foot_arconic_media.css',
    ];

    public $js = [
    ];

//    public $jsOptions = [
//        'position'=>View::POS_HEAD
//    ];

//    public $depends = [
//        'skewer\build\Page\Main\PrintAsset',
//    ];
}
