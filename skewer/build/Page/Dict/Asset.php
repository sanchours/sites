<?php

namespace skewer\build\Page\Dict;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle {

    public $sourcePath = '@skewer/build/Page/Dict/web/';

    public $css = [
        'css/dict.css',
    ];

    public $js = [
        'js/dict.js',
    ];

}
