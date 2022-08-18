<?php

namespace skewer\build\Page\Main\templates\footer\foot_bakery;

use yii\web\AssetBundle;

/**
 * #57391.
 * Class ExampleAsset.
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/footer/foot_bakery/web/';

    public $css = [
        'css/foot_bakery.css',
        'css/foot_bakery_media.css',
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
