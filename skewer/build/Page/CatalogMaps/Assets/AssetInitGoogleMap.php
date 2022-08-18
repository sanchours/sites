<?php

namespace skewer\build\Page\CatalogMaps\Assets;

use skewer\base\SysVar;
use skewer\build\Page\CatalogMaps\Api;
use yii\web\AssetBundle;
use yii\web\View;

class AssetInitGoogleMap extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/CatalogMaps/web/';

    public $js = [
        'js/google_maps.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];

    public function init()
    {
        if (SysVar::get(Api::getSysVarName(Api::providerGoogleMap, 'clusterize'), false) == true) {
            $this->depends[] = 'skewer\libs\GoogleMarkerClusterer\Asset';
        }

        parent::init();
    }
}
