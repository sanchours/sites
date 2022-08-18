<?php

namespace skewer\build\Page\Poll;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Poll/web/';

    public $css = [
        'css/poll.css',
    ];

    public $js = [
        'js/Poll.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
