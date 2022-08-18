<?php

namespace skewer\build\Page\CatalogMaps\Assets;

use yii\web\AssetBundle;
use yii\web\View;

class AssetInitYandexMap extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/CatalogMaps/web/';

    public $js = [
        'js/yandex_maps.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
