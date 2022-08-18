<?php

namespace skewer\build\Page\News;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/News/web/';

    public $css = [
        'css/news.css',
        'css/news_media.css',
    ];

    public $js = [
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'skewer\libs\owlcarousel\Asset',
        'skewer\build\Page\GalleryViewer\Asset',
    ];
}
