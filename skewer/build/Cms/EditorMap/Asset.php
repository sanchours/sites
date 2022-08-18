<?php

namespace skewer\build\Cms\EditorMap;

use skewer\build\Page\CatalogMaps\Api;
use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Cms/EditorMap/web';

    public $js = [
        'js/editor.js',
    ];

    public $css = [
        'css/map.css',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public function init()
    {
        if (Api::getActiveProvider() == Api::providerYandexMap) {
            array_unshift($this->js, 'js/yandex.js');
        } elseif (Api::getActiveProvider() == Api::providerGoogleMap) {
            array_unshift($this->js, 'js/google.js');
        }

        parent::init();
    }
}
