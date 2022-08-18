<?php

namespace skewer\build\Page\Articles;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Articles/web/';

    public $css = [
        'css/articles.css',
        'css/articles_media.css',
    ];
}
