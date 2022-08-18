<?php

namespace skewer\components\filters\widgets;

use yii\helpers\ArrayHelper;

/**
 * Class NumSlider - виджет слайдер цены.
 */
class NumSlider extends Prototype
{
    public function parse($aFilterFieldData)
    {
        $aMinMaxValues = $this->getMinMaxValues();

        if ($aMinMaxValues === false) {
            return false;
        }

        return [
            'title' => $this->getFieldTitle(),
            'name' => $this->getFieldName(),
            'type' => self::getTypeWidget(),
            'def_value_min' => $aMinMaxValues['min'],
            'def_value_max' => $aMinMaxValues['max'],
            'value_min' => ArrayHelper::getValue($aFilterFieldData, 'min', $aMinMaxValues['min']),
            'value_max' => ArrayHelper::getValue($aFilterFieldData, 'max', $aMinMaxValues['max']),
            'inputMaskAlias' => $this->getInputMaskOptions(),
        ];
    }

    public static function getTypeWidget()
    {
        return 'num_slider';
    }

    public function canHaveTitle()
    {
        return false;
    }

    /**
     * Получить минимальное и максимальное значение поля.
     *
     * @return array
     */
    private function getMinMaxValues()
    {
        $sTable = $this->oField->getFtField()->getModel()->getTableName();
        $sField = $this->getFieldName();

        $query = $this->oFilter->getQuery();

        $query
            ->fields("max({$sTable}.{$sField}) as vmax, min({$sTable}.{$sField}) as vmin", true)
            ->join('inner', 'c_goods', '', $sTable . '.id=base_id')
            ->on('base_id=parent')
            ->where('active', 1);

        $row = $query->asArray()->get();

        if ($row[0]['vmin'] === $row[0]['vmax']) {
            return false;
        }

        $out = ['min' => 0, 'max' => 1000];

        if ($row) {
            $out['min'] = floor($row[0]['vmin']);
            $out['max'] = ceil($row[0]['vmax']);
        }

        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function canonizeValue($mDataItem)
    {
        $mDataItem = parent::canonizeValue($mDataItem);

        return [
            'min' => min($mDataItem),
            'max' => max($mDataItem),
        ];
    }

    /** {@inheritdoc} */
    public function filterInputVal($aDataItem)
    {
        $aMinMaxValues = $this->getMinMaxValues();

        if ($aMinMaxValues === false) {
            return false;
        }

        if (($aDataItem['min'] == $aMinMaxValues['min']) && ($aDataItem['max'] == $aMinMaxValues['max'])) {
            return false;
        }

        return true;
    }
}
