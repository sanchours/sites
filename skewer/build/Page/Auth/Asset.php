<?php

namespace skewer\build\Page\Auth;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Auth/web/';

    public $css = [
        'css/authmain.css',
    ];

    public $js = [
        'js/auth.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
