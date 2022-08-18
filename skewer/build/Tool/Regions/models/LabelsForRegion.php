<?php

namespace skewer\build\Tool\Regions\models;

use skewer\build\Tool\Labels\models\Labels;
use skewer\components\regions\models\RegionLabels;
use skewer\components\regions\models\Regions;

/**
 * Class LabelForRegion.
 */
class LabelsForRegion extends Labels
{
    public $regionId;
    public $labelValue;

    private static function getLabelsForRegion($idRegion)
    {
        return static::find()
            ->select([
                'regionId' => 'regions.id',
                'id' => 'labels.id',
                'title' => 'labels.title',
                'alias' => 'labels.alias',
                'default' => 'labels.default',
                'labelValue' => 'COALESCE(region_labels.value , NULL)',
            ])
            ->join('CROSS JOIN', Regions::tableName())
            ->leftJoin(
                RegionLabels::tableName(),
                '`region_labels`.`region_id` = `regions`.`id` 
                AND `region_labels`.`label_id` = `labels`.`id`'
            )
            ->where(['`regions`.`id`' => $idRegion]);
    }

    /**
     * Получение меток в соответствии с регионом
     *
     * @param $idRegion
     *
     * @return array
     */
    public static function getLabels($idRegion)
    {
        /** @var LabelsForRegion[] $labels */
        $labels = self::getLabelsForRegion($idRegion)->all();

        $result = [];

        foreach ($labels as $key => $value) {
            $attributes = $value->getAttributes();

            $attributes['defaultValueReplaced'] = $value->labelValue === null ? false : true;

            $attributes['default'] = trim(
                htmlspecialchars(
                    $value->labelValue === null ? $attributes['default'] : $value->labelValue
                )
            );

            $attributes['regionId'] = $value->regionId;

            $result[$key] = $attributes;
        }

        return $result;
    }
}
