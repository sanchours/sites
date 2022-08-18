<?php

namespace skewer\build\Page\CatalogMaps;

use skewer\base\ft\Editor;
use skewer\base\ft\model\Field;
use skewer\base\SysVar;
use skewer\build\Page\CatalogMaps\models\GeoObjects;
use skewer\build\Page\CatalogMaps\models\Maps;
use skewer\components\catalog\Card;
use skewer\components\catalog\field\MapSingleMarker;
use skewer\components\catalog\GoodsSelector;
use skewer\libs\GoogleMarkerClusterer;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\StringHelper;

class Api
{
    /** @const Тех.имя провайдера карт - Yandex */
    const providerYandexMap = 'yandex';

    /** @const Тех.имя провайдера карт - Google */
    const providerGoogleMap = 'google';

    /** @const Имя параметра "Выводить модификации" в редакторе */
    const paramNameShowModification = 'bShowModification';

    /**
     * Вернёт глобальные настройки выбранного типа карты.
     *
     * @param string $sTypeMap - тип карты
     *
     * @return array
     */
    public static function getGlobalSettingMap($sTypeMap)
    {
        return [
          'clusterize' => (bool) SysVar::get(self::getSysVarName($sTypeMap, 'clusterize'), false),
          'iconMarkers' => SysVar::get(self::getSysVarName($sTypeMap, 'iconMarkers'), ''),
        ];
    }

    /**
     * Список провайдеров карт
     *
     * @return array
     */
    public static function getMapProviders()
    {
        return [
            self::providerYandexMap => 'Yandex',
            self::providerGoogleMap => 'Google',
        ];
    }

    /**
     * Получить активного провайдера карт
     *
     * @return string
     */
    public static function getActiveProvider()
    {
        return SysVar::get('Maps.type_map', '');
    }

    public static function isActiveFieldYandexCard(Field $field): bool
    {
        return $field->getEditorName() == Editor::MAP_SINGLE_MARKER
            && $field->getAttr('active')
            && self::getActiveProvider() == self::providerYandexMap;
    }

    /**
     * Можно ли выводить карту?
     *
     * @param $aErrors - ошибки
     *
     * @return bool
     */
    public static function canShowMap(&$aErrors = [])
    {
        if (!($sProvider = self::getActiveProvider())) {
            $aErrors[] = \Yii::t('editorMap', 'map_is_not_selected');

            return false;
        }

        if ($sProvider == self::providerGoogleMap) {
            if (SysVar::get(Api::getSysVarName(Api::providerGoogleMap, 'api_key'), '')) {
                return true;
            }

            $aErrors[] = \Yii::t('editorMap', 'not_found_apikey_google');

            return false;
        } else {
            if (SysVar::get(Api::getSysVarName(Api::providerYandexMap, 'api_key'), '')) {
                return true;
            }

            $aErrors[] = \Yii::t('editorMap', 'not_found_apikey_yandex');

            return false;
        }

        return true;
    }

    /**
     * Получить полное имя SysVar переменной.
     *
     * @param string $sProviderMap - провайдер карты
     * @param string $sParamName   - название параметра
     *
     * @return string
     */
    public static function getSysVarName($sProviderMap, $sParamName)
    {
        $sLanguageCategory = 'Maps';

        return $sLanguageCategory . '.' . $sProviderMap . '_' . $sParamName;
    }

    /**
     * Вернёт урл скрипта активного провайдера карт с подключенной библиотекой поиска
     * или false - если не выбран провайдер
     *
     * @param string $sLang - текущий язык
     *
     * @return bool|string
     */
    public static function getUrlMapScriptWithSearch($sLang = 'en')
    {
        $sTypeProvider = self::getActiveProvider();

        switch ($sTypeProvider) {
            case self::providerGoogleMap:
                $sUrl = Assets\AssetGoogleMap::buildSrcScript(['places']);

                break;
            case self::providerYandexMap:
                $sUrl = Assets\AssetYandexMap::buildUrlScript($sLang);

                break;
            default:
                $sUrl = false;
        }

        return $sUrl;
    }

    /**
     * Построит карту.
     *
     * @param array $aMarkers  - массив маркеров
     * @param null|int $iMapId - id карты
     *
     * @return bool|string - вернет результат html с картой или false - в случае ошибки
     */
    public static function buildMap($aMarkers = [], $iMapId = null)
    {
        if (!self::canShowMap()) {
            return false;
        }

        $sTypeMap = self::getActiveProvider();

        // Глобальные настройки модуля
        $aGlobalSettingsMaps = Api::getGlobalSettingMap($sTypeMap);

        // Локальные настройки модуля
        $aLocalSettingsMaps = [];

        if ($iMapId) {
            $aLocalSettingsMaps = Maps::getSettingsMapById($iMapId);
        }

        $aSettingsMaps = ArrayHelper::merge($aGlobalSettingsMaps, $aLocalSettingsMaps, Api::getDefaultCenterMap());

        $aSettings = [
            'markers' => $aMarkers,
            'mapSettings' => $aSettingsMaps,
        ];

        return \Yii::$app->getView()->renderPhpFile(
            __DIR__ . \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'map.php',
            [
                'settings' => Json::htmlEncode($aSettings),
                'MarkerClusterAssetUrl' => \Yii::$app->assetManager->getPublishedUrl((new GoogleMarkerClusterer\Asset())->sourcePath),
            ]
        );
    }

    /**
     * Вернет маркеры для карты из товаров $aSections разделов.
     *
     * @param array $aSections - каталожные разделы
     * @param string $sPathTemplate4PopupMessage - путь к шаблону всплывающего сообщения
     * @param bool $bShowModification - выводить товары модификации ?
     *
     * @return array массив маркеров
     */
    public static function getMarkersFromCatalogSections(array $aSections, $sPathTemplate4PopupMessage = '', $bShowModification = false)
    {
        if (!$aSections) {
            return [];
        }

        $aGoods = GoodsSelector::getList4Section($aSections, ['active'], $bShowModification)
            ->condition('active', 1)
            ->parse();
        $aMarkers = [];

        foreach ($aGoods as $aGood) {
            $aGood = MapSingleMarker::parseCatalogFields4Map($aGood);

            foreach ($aGood['fields'] as $aField) {
                if ($aField['type'] === Editor::MAP_SINGLE_MARKER) {
                    $iLatitude = ArrayHelper::getValue($aField, 'latitude', null);
                    $iLongitude = ArrayHelper::getValue($aField, 'longitude', null);

                    if ($iLatitude === null || $iLongitude === null) {
                        continue;
                    }

                    $aMarker = [
                        'latitude' => $iLatitude,
                        'longitude' => $iLongitude,
                        'title' => $aGood['title'],
                    ];

                    if ($sPathTemplate4PopupMessage) {
                        $aMarker['popup_message'] = \Yii::$app->getView()->renderPhpFile(
                            $sPathTemplate4PopupMessage,
                            ['aGood' => $aGood, 'reedMore' => !Card::isDetailHiddenByCard($aGood['card'])]
                        );
                    }

                    $aMarkers[] = $aMarker;
                }
            }
        }

        return $aMarkers;
    }

    /**
     * Вернёт форматированный адрес геообъекта.
     *
     * @param int $iGeoObjId - id геообъекта
     *
     * @return string
     */
    public static function getAddressGeoObjectFormatted($iGeoObjId)
    {
        if ($oGeoObj = GeoObjects::findOne($iGeoObjId)) {
            return $oGeoObj->getAddressGeoObjectFormatted();
        }

        return '';
    }

    /**
     * Извлекает id геообъекта из его адреса.
     *
     * @param $sAddress string
     *
     * @return bool|int
     */
    public static function extractGeoObjectIdFromAddress($sAddress)
    {
        $aMatches = [];
        preg_match('/[^;]*;\s*\[id=([0-9]*)\]/i', $sAddress, $aMatches);

        return (isset($aMatches[1]))
            ? (int) $aMatches[1]
            : false;
    }

    /**
     * Вернет координаты и зум города по умолчанию.
     * @return array
     */
    public static function getDefaultCenterMap()
    {
        if ($iCenterMap = SysVar::get('Maps.default_center')) {
            $aMap = GeoObjects::getMapByGeoObjectId($iCenterMap);

            $aCoords = StringHelper::explode($aMap['center'], ',', true, true);

            return  [
                'defaultCenterMap' => [
                    'lat' => $aCoords[0],
                    'lng' => $aCoords[1],
                    'zoom' => (int) $aMap['zoom'],
                ],
            ];
        }
        // Москва
        return [
                'defaultCenterMap' => [
                    'lat' => 55.755814,
                    'lng' => 37.617635,
                    'zoom' => 10,
                ],
            ];
    }

    /**
     * Копирует/клонирует геообъект вместе с картой.
     *
     * @param int $iGeoObjectId - id геообекта
     *
     * @return int | bool id нового геообъекта или false, если копирование не удалось
     */
    public static function copyGeoObjectIdWithMap($iGeoObjectId)
    {
        if (!($oOriginGeoObj = GeoObjects::findOne($iGeoObjectId))) {
            return false;
        }

        if (!($oMap = Maps::findOne($oOriginGeoObj->map_id))) {
            return false;
        }

        // Копируем карту
        $oNewMap = new Maps();
        $oNewMap->setAttributes($oMap->getAttributes(null, ['id']));

        if (!$oNewMap->save()) {
            return false;
        }

        // Копируем геообъект
        $oNewGeoObj = new GeoObjects();
        $oNewGeoObj->setAttributes($oOriginGeoObj->getAttributes(null, ['id', 'map_id']));
        $oNewGeoObj->map_id = $oNewMap->id;
        $oNewGeoObj->save();

        return $oNewGeoObj->id;
    }
}
