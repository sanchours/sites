<?php

namespace skewer\build\Page\Subscribe;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Subscribe/web/';

    public $js = [
        'js/Subscribe.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
