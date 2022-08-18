<?php

namespace skewer\components\regions\models;

use skewer\components\ActiveRecord\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "region_labels".
 *
 * @property int $id
 * @property int $region_id
 * @property int $label_id
 * @property string $value
 */
class RegionLabels extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%region_labels}}';
    }

    public static function createNewOnLabelRegion($idLabel, $idRegion)
    {
        $valueLabel = new RegionLabels();
        $valueLabel->label_id = $idLabel;
        $valueLabel->region_id = $idRegion;

        return $valueLabel;
    }

    public static function getByLabelAndRegion($labelId, $regionId)
    {
        return RegionLabels::findOne([
            'region_id' => $regionId,
            'label_id' => $labelId,
        ]);
    }

    /**
     * Получение измененных меток для региона, формат idLabel => value.
     *
     * @param $idRegion
     *
     * @return array
     */
    public static function getReplaceLabelForRegion($idRegion)
    {
        $values = RegionLabels::find()
            ->select(['label_id', 'value'])
            ->where(['region_id' => $idRegion])
            ->andWhere("value != ''")
            ->asArray()
            ->all();

        return ArrayHelper::map($values, 'label_id', 'value');
    }

    public static function deleteByLabelId($labelId)
    {
        return self::deleteAll(['label_id' => $labelId]);
    }

    public static function deleteByRegionId($regionId)
    {
        return self::deleteAll(['region_id' => $regionId]);
    }

    public function rules()
    {
        return [
            [['region_id', 'label_id'], 'required'],
            [['region_id', 'label_id'], 'integer'],
            [['value'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region_id' => 'Region ID',
            'label_id' => 'Label ID',
            'value' => 'Value',
        ];
    }
}
