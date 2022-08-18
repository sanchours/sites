<?php

namespace skewer\components\catalog\field;

use skewer\base\ft\Editor;
use skewer\base\site\Layer;
use skewer\base\site_module;
use skewer\build\Page\CatalogMaps;
use skewer\build\Page\CatalogMaps\models\GeoObjects;
use yii\helpers\ArrayHelper;

class MapSingleMarker extends Prototype
{
    /**
     * {@inheritdoc}
     */
    protected function build($value, $rowId, $aParams)
    {
        $aOut = [
            'value' => $value,
            'html' => $value,
        ];

        if ($value && ($oGeoObject = GeoObjects::findOne($value))) {
            $aOut['latitude'] = $oGeoObject->latitude;
            $aOut['longitude'] = $oGeoObject->longitude;
            $aOut['map_id'] = $oGeoObject->map_id;
            $aOut['address'] = $oGeoObject->address;
        }

        return $aOut;
    }

    /**
     * {@inheritdoc}
     */
    public function afterParseGood($aGoodData, $aFieldData)
    {
        if (empty($aFieldData['latitude']) && empty($aFieldData['longitude']) && empty($aFieldData['map_id'])) {
            $aFieldData['html'] = $aFieldData['value'] = '';

            return $aFieldData;
        }

        $sCatMapsTplDir = site_module\Module::getTemplateDir4Module(CatalogMaps\Module::getNameModule(), Layer::PAGE);

        $aMarker = [
            'latitude' => ArrayHelper::getValue($aFieldData, 'latitude'),
            'longitude' => ArrayHelper::getValue($aFieldData, 'longitude'),
            'title' => ArrayHelper::getValue($aGoodData, 'title', ''),
            'popup_message' => \Yii::$app->getView()->renderPhpFile(
                $sCatMapsTplDir . 'infoWindow.php',
                ['aGood' => self::parseCatalogFields4Map($aGoodData), 'reedMore' => false]
            ),
        ];

        $iMapId = ArrayHelper::getValue($aFieldData, 'map_id');

        $sHtml = CatalogMaps\Api::buildMap([$aMarker], $iMapId);

        if ($sHtml === false) {
            $aFieldData['html'] = $aFieldData['tab'] = $aFieldData['value'] = '';
        } else {
            $aFieldData['html'] = $aFieldData['tab'] = $sHtml;
        }

        return $aFieldData;
    }

    /**
     * Парсит каталожные поля для использования в картах во всплывающем окне.
     *
     * @param $aGoodData array - данные товара
     *
     * @return array
     */
    public static function parseCatalogFields4Map($aGoodData)
    {
        foreach ($aGoodData['fields'] as &$aField) {
            $sVal = '';
            switch ($aField['type']) {
                case Editor::MAP_SINGLE_MARKER:
                    $sVal = ArrayHelper::getValue($aField, 'address');
                    break;
                case Editor::MONEY:
                    $sVal = sprintf('%s %s', \skewer\base\Twig::priceFormat(ArrayHelper::getValue($aField, 'value')), ArrayHelper::getValue($aField, 'measure'));
                    break;
                case Editor::SELECT:
                case Editor::COLLECTION:
                    $sVal = ArrayHelper::getValue($aField, 'item.title');
                    break;
                case Editor::MULTICOLLECTION:
                case Editor::MULTISELECT:
                    $sVal = implode(', ', ArrayHelper::getColumn($aField['item'], 'title'));
                    break;
                case Editor::MULTISELECTIMAGE:
                case Editor::SELECTIMAGE:
                    $sVal = $aField['tab'];
                    break;
                case Editor::DATE:
                    $sVal = date('d.m.Y', strtotime(ArrayHelper::getValue($aField, 'value')));
                    break;
                case Editor::DATETIME:
                    $sVal = date('d.m.Y H:i', strtotime(ArrayHelper::getValue($aField, 'value')));
                    break;
                case Editor::CHECK:
                    $sVal = ArrayHelper::getValue($aField, 'value') ? \Yii::t('page', 'yes') : \Yii::t('page', 'no');
                    break;
                case Editor::FILE:
                    $sVal = ArrayHelper::getValue($aField, 'tab');
                    break;
                case Editor::GALLERY:

                    if (!empty($aField['gallery']['images'])) {
                        $sSrc = ArrayHelper::getValue($aField, 'gallery.images.0.images_data.mini.file', '');
                        $sVal = "<img src=\"{$sSrc}\" alt=\"\" />";
                    }

                    break;

                case Editor::STRING:
                case Editor::WYSWYG:
                case Editor::TEXT:
                case Editor::INTEGER:
                case Editor::FLOAT:
                default:
                    $sVal = ArrayHelper::getValue($aField, 'value');
            }

            $aField['html_map'] = $sVal;
        }

        return $aGoodData;
    }
}
