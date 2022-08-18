<?php

namespace skewer\build\Page\CatalogMaps\models;

use skewer\components\ActiveRecord\ActiveRecord;
use Yii;

/**
 * This is the model class for table "geoObjects".
 *
 * @property int $id
 * @property int $map_id
 * @property string $latitude
 * @property string $longitude
 * @property string $address
 * @property Maps $map
 */
class GeoObjects extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'geoObjects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['latitude', 'longitude', 'map_id'], 'required'],
            [['latitude', 'longitude'], 'trim'],
            [['address'], 'string'],
            [['latitude'], 'double', 'min' => -90, 'max' => 90],
            [['longitude'], 'double', 'min' => -180, 'max' => 180],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('maps', 'ID'),
            'map_id' => Yii::t('maps', 'map_id'),
            'latitude' => Yii::t('maps', 'latitude'),
            'longitude' => Yii::t('maps', 'longitude'),
            'address' => Yii::t('maps', 'address'),
        ];
    }

    public function getMap()
    {
        return $this->hasOne(Maps::class, ['id' => 'map_id']);
    }

    public function setMap(Maps $map)
    {
        $this->map = $map;
    }

    /**
     * Вернёт карту геообъекта.
     *
     * @param $iGeoObjectId int
     *
     * @return array
     */
    public static function getMapByGeoObjectId($iGeoObjectId)
    {
        return self::find()
            ->select('maps.*')
            ->where(['geoObjects.id' => $iGeoObjectId])
            ->innerJoin(Maps::tableName(), 'geoObjects.map_id = maps.id')
            ->asArray()
            ->one();
    }

    public function getAddressGeoObjectFormatted()
    {
        $sAddress = $this->address
            ? $this->address
            : sprintf('%f,%f', $this->latitude, $this->longitude);

        return sprintf('%s; [id=%d]', $sAddress, $this->id);
    }
}
