<?php

namespace skewer\build\Page\CatalogMaps\Assets;

use skewer\base\SysVar;
use skewer\build\Page\CatalogMaps\Api;
use yii\web\AssetBundle;
use yii\web\View;

class AssetYandexMap extends AssetBundle
{
    /** @const string Url не коммерческой версии Api */
    const not_commercial_version = 'https://api-maps.yandex.ru/2.1/';

    /** @const string Url коммерческой версии Api */
    const commercial_version = 'https://enterprise.api-maps.yandex.ru/2.1/';

    public $sourcePath = '@skewer/build/Page/CatalogMaps/web/';

    public $js = [];

    public $css = [
        'css/map.css',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];

    public $depends = [
        'skewer\build\Page\CatalogMaps\Assets\AssetInitYandexMap',
    ];

    public function init()
    {
        $this->js[] = self::buildUrlScript(\Yii::$app->i18n->getTranslateLanguage());

        parent::init();
    }

    /**
     * Вернет локаль по языку.
     *
     * @param string $sLanguage      - текущий язык
     * @param string $sDefaultLocale - локаль по умолчанию
     *
     * @return string
     */
    private static function getLocaleByLanguage($sLanguage, $sDefaultLocale = 'en_US')
    {
        $aLocale = [
            'ru' => 'ru_RU',
            'en' => 'en_US',
        ];

        return $aLocale[$sLanguage] ?? $sDefaultLocale;
    }

    /**
     * Строит url скрипта.
     *
     * @param string $sLang     - текущий язык
     * @param array $aLibraries - подключаемые библиотеки.
     *              'package.full' - подключен весь пакет библиотек(как говорит документации - это не влияет на скорость)
     *
     * @return string
     */
    public static function buildUrlScript($sLang = 'en', $aLibraries = ['package.full'])
    {
        $bIsCommercialVersion = false;

        // Параметры запроса
        $aQueryParams = [
            'lang' => self::getLocaleByLanguage($sLang),
            'onload' => 'initMap',
        ];

        if ($aLibraries) {
            $aQueryParams['load'] = implode(',', $aLibraries);
        }

        if ($sApiKey = trim(SysVar::get(Api::getSysVarName(Api::providerYandexMap, 'api_key'), ''))) {
            $bIsCommercialVersion = true;
            $aQueryParams['apikey'] = $sApiKey;
        }

        $sSrcScript = ($bIsCommercialVersion) ? self::commercial_version : self::not_commercial_version;

        $sSrcScript .= '?' . http_build_query($aQueryParams);

        return $sSrcScript;
    }
}
