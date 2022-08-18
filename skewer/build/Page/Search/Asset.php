<?php

namespace skewer\build\Page\Search;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Search/web/';

    public $css = [
        'css' => 'css/search.css',
    ];

    public $js = [
        'js' => 'js/search.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
