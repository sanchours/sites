<?php

namespace skewer\build\Page\Menu;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Menu/web/';

    public $css = [
        'css/menu.css',
    ];
}
