<?php

namespace skewer\build\Page\Profile;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Profile/web/';

    public $css = [
        'css/profile.css',
        'css/profile-ui.css',
        'css/profile_media.css',
    ];

    public $js = [
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\build\Page\CatalogViewer\Asset',
    ];
}
