<?php

namespace skewer\components\rating;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/components/rating/web/';

    public $css = [
        'css/rating.css',
    ];

    public $js = [
        'js/rating.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
