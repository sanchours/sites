<?php

namespace skewer\build\Page\Copyright;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Copyright/web/';

    public $js = [
        'js/antiCopyright.js',
    ];
}
