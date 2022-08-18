<?php

namespace skewer\build\Page\CatalogMaps\Assets;

use skewer\base\SysVar;
use skewer\build\Page\CatalogMaps\Api;
use yii\web\AssetBundle;
use yii\web\View;

class AssetGoogleMap extends AssetBundle
{
    /** @cosnt string Url исходника Google Maps Api */
    const sourceScript = 'https://maps.googleapis.com/maps/api/js';

    public $sourcePath = '@skewer/build/Page/CatalogMaps/web/';

    public $js = [];

    public $css = [
        'css/map.css',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
        'async' => true,
        'defer' => true,
    ];

    public $depends = [
        'skewer\build\Page\CatalogMaps\Assets\AssetInitGoogleMap',
    ];

    public function init()
    {
        $this->js[] = self::buildSrcScript();

        parent::init();
    }

    /**
     * Строит url скрипта.
     *
     * @param array $aLibraries - подключаемые библиотеки
     *
     * @return string
     */
    public static function buildSrcScript($aLibraries = [])
    {
        // Параметры запроса
        $aQueryParams = [
            'key' => trim(SysVar::get(Api::getSysVarName(Api::providerGoogleMap, 'api_key'), '')),
            'callback' => 'initMap',
        ];

        if ($aLibraries) {
            $aQueryParams['libraries'] = implode(',', $aLibraries);
        }

        $sSrcScript = self::sourceScript . '?' . http_build_query($aQueryParams);

        return $sSrcScript;
    }
}
