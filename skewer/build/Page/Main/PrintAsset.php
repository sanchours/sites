<?php

namespace skewer\build\Page\Main;

use yii\web\AssetBundle;

class PrintAsset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/web/';

    public $css = [
        'css/print.css',
    ];

    public $cssOptions = [
        'media' => 'print',
    ];
}
