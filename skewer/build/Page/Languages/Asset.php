<?php

namespace skewer\build\Page\Languages;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Languages/web/';

    public $css = [
        'css/language.css',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
