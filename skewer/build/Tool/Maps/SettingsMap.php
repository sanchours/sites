<?php

namespace skewer\build\Tool\Maps;

use skewer\base\SysVar;
use skewer\build\Page\CatalogMaps\Api;
use skewer\build\Page\CatalogMaps\models\GeoObjects;
use yii\base\Model;

/**
 * Class SettingsMap
 * @package skewer\build\Tool\Maps
 */
class SettingsMap extends Model
{
    public $typeMap;
    public $apiKey;
    public $iconMarkers;
    public $clusterize;

    private $languageCategory;
    /** @var GeoObjects $geoObject */
    private $geoObject;

    /** @var YandexSettingsMap $geoObject */
    private $yandexGeoObject;

    /** настройки для Yandex */
    public $coordinates;
    public $center;
    public $zoom;
    public $address;

    /** настройки для Google */
    public $defaultCenter;

    public function rules()
    {
        return [
            [['clusterize'], 'boolean'],
            [
                [
                    'typeMap',
                    'apiKey',
                    'iconMarkers',
                    'coordinates',
                    'center',
                    'address',
                    'defaultCenter'
                ],
                'string'
            ],
            [['zoom'], 'integer'],
        ];
    }

    public function __construct(
        string $typeMap,
        string $languageCategory,
        $config = []
    ) {
        parent::__construct($config);

        $this->typeMap = $typeMap;

        $this->apiKey = SysVar::get(
            Api::getSysVarName($this->typeMap, 'api_key'),
            ''
        );
        $this->iconMarkers = SysVar::get(
            Api::getSysVarName($this->typeMap, 'iconMarkers'),
            ''
        );
        $this->clusterize = SysVar::get(
            Api::getSysVarName($this->typeMap, 'clusterize'),
            false
        );

        $this->languageCategory = $languageCategory;
        $this->setGeoObjData($languageCategory);
    }

    public function save()
    {
        SysVar::set($this->languageCategory . '.type_map', $this->typeMap);

        if ($this->typeMap) {
            SysVar::set(
                Api::getSysVarName($this->typeMap, 'api_key'),
                $this->apiKey
            );
            SysVar::set(
                Api::getSysVarName($this->typeMap, 'iconMarkers'),
                $this->iconMarkers
            );
            SysVar::set(
                Api::getSysVarName($this->typeMap, 'clusterize'),
                $this->clusterize
            );
        }

        $methodSet = "saveSettings{$this->typeMap}";
        if (method_exists($this, $methodSet)) {
            $this->{$methodSet}();
        }

    }

    /**
     * Установка настроек для определенной карты
     * @param string $languageCategory
     */
    private function setGeoObjData(string $languageCategory)
    {
        $geoObjId = SysVar::get($languageCategory . '.default_center');
        if (empty($geoObjId)) {
            $geoObjId = null;
        }
        $this->yandexGeoObject = new YandexSettingsMap($geoObjId);
        $this->geoObject = $this->yandexGeoObject->getGeoObject();

        if ($this->geoObject instanceof GeoObjects) {
            $methodSet = "setSettings{$this->typeMap}";
            if (method_exists($this, $methodSet)) {
                $this->{$methodSet}();
            }
        }
    }

    private function setSettingsYandex()
    {
        $this->setAttributes($this->yandexGeoObject->getAttributes());
    }

    private function setSettingsGoogle()
    {
        $this->defaultCenter = $this->geoObject->getAddressGeoObjectFormatted();
    }

    /**
     * @throws \Exception
     */
    private function saveSettingsYandex()
    {
        $this->yandexGeoObject->setAttributes($this->getAttributes());

        if (!$this->yandexGeoObject->save()) {
            throw new \Exception(
                current($this->yandexGeoObject->getFirstErrors())
            );
        }

        SysVar::set(
            $this->languageCategory . '.default_center',
            $this->yandexGeoObject->getGeoObject()->id
        );
    }

    private function saveSettingsGoogle()
    {
        $defaultCenter = Api::extractGeoObjectIdFromAddress($this->defaultCenter);

        if ($defaultCenter === false) {
            $defaultCenter = '';
        }

        SysVar::set(
            $this->languageCategory . '.default_center',
            $defaultCenter
        );
    }

}
