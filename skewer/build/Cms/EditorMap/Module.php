<?php

namespace skewer\build\Cms\EditorMap;

use skewer\base\ui\ARSaveException;
use skewer\build\Cms;
use skewer\build\Page\CatalogMaps\Api;
use skewer\build\Page\CatalogMaps\models\GeoObjects;
use skewer\build\Page\CatalogMaps\models\Maps;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/**
 * Модуль для отображения карт
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        $aErrors = [];
        if (!Api::canShowMap($aErrors)) {
            $this->addError(reset($aErrors));

            return false;
        }

        $this->setModuleLangValues([
            'all_fields_required' => 'all_fields_required',
            'invalid_data_format' => 'invalid_data_format',
            'error_longtitude_range' => 'error_longtitude_range',
            'error_latitude_range' => 'error_latitude_range',
        ]);

        $this->setCmd('init');
    }

    protected function actionEdit()
    {
        // Режим работы карты
        $sMapMode = $this->getStr('mapMode', '');

        $aMarkers = $aMapSettings = [];

        switch ($sMapMode) {
            case 'single':

                $iGeoObjectId = $this->getInt('geoObjectId');

                if ($iGeoObjectId && ($oGeoObject = GeoObjects::findOne($iGeoObjectId))) {
                    $aMarkers[] = [
                        'latitude' => $oGeoObject->latitude,
                        'longitude' => $oGeoObject->longitude,
                    ];

                    $aMapSettings = Maps::getSettingsMapById($oGeoObject->map_id);
                }
                $aMapSettings += Api::getDefaultCenterMap();
                $aCapabilities = ['addMarkerFromInput' => true, 'addMarkerByClick' => true, 'searchLine' => true];

                $aParams = [
                    'settings' => Json::htmlEncode($aMapSettings),
                    'markers' => Json::htmlEncode($aMarkers),
                    'capabilities' => Json::htmlEncode($aCapabilities),
                    'showSearchLine' => true,
                    'showSetMarkerForm' => true,
                ];

                break;

            case 'list':

                $sEntities = $this->getStr('entities');
                $iMapId = $this->getInt('mapId');
                $bShowModification = (bool) $this->getInt('showModification', 0);

                $aCatalogSections = StringHelper::explode(urldecode($sEntities), ',', true, true);

                $aMarkers = Api::getMarkersFromCatalogSections($aCatalogSections, '', $bShowModification);

                $aMapSettings = Maps::getSettingsMapById($iMapId);
                $aMapSettings += Api::getDefaultCenterMap();

                $aCapabilities = ['addMarkerFromInput' => false, 'addMarkerByClick' => false, 'searchLine' => false];

                $aParams = [
                    'settings' => Json::htmlEncode($aMapSettings),
                    'markers' => Json::htmlEncode($aMarkers),
                    'capabilities' => Json::htmlEncode($aCapabilities),
                    'showSearchLine' => false,
                    'showSetMarkerForm' => false,
                ];

                break;
            default:
                return false;
        }

        $sHtml = $this->renderTemplate('map.php', $aParams);

        $this->setCmd('edit');

        $this->setData('html', $sHtml);
        $this->setData('urlScript', Api::getUrlMapScriptWithSearch(\Yii::$app->i18n->getTranslateLanguage()));
    }

    /** Сохранение состояния карты */
    protected function actionSave()
    {
        // Режим работы карты
        $sMapMode = $this->getStr('mapMode', '');

        switch ($sMapMode) {
            case 'single':
                $iGeoObjectId = $this->saveSingleMarker();
                $iRowId = Api::getAddressGeoObjectFormatted($iGeoObjectId);
                break;
            case 'list':
                $iRowId = $this->saveListMarkers();
                break;
            default:
                return false;
        }

        $this->setData('iRowId', $iRowId);
        $this->setCmd('save');
    }

    /**
     * Сохранение состояния карты(положения маркера + область вывода карты).
     */
    protected function saveSingleMarker()
    {
        try {
            $aMapData = $this->get('mapData');
            $aGeoData = $this->get('geoData');
            $iGeoObjectId = $this->getInt('geoObjectId');

            if ($iGeoObjectId && ($oGeoObject = GeoObjects::findOne($iGeoObjectId))) {
                $oMap = Maps::getNewOrExist($oGeoObject->map_id);
                $oMap->setAttributes($aMapData);
                $oMap->save();

                $oGeoObject->setAttributes($aGeoData);

                if (!$oGeoObject->save()) {
                    throw new ARSaveException($oGeoObject);
                }
            } else {
                $oMap = new Maps($aMapData);
                $oMap->save();

                $oGeoObject = new GeoObjects($aGeoData);
                $oGeoObject->map_id = $oMap->id;

                if (!$oGeoObject->save()) {
                    throw new ARSaveException($oGeoObject);
                }
            }

            return $oGeoObject->id;
        } catch (ARSaveException $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * Сохранение состояния карты(области её вывода).
     */
    protected function saveListMarkers()
    {
        $aMapData = $this->get('mapData');
        $iMapId = $this->getInt('mapId');

        $oMapRow = Maps::getNewOrExist($iMapId);
        $oMapRow->setAttributes($aMapData);
        $oMapRow->save();

        return $oMapRow->id;
    }
}
