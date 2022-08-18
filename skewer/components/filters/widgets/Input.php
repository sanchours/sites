<?php

namespace skewer\components\filters\widgets;

/**
 * Class Input - виджет input.
 */
class Input extends Prototype
{
    public function parse($aFilterFieldData)
    {
        $arr = $this->oFilter->getUniqueValues4FilterField($this->oField);

        if (!$arr) {
            return [];
        }

        if (count($arr) == 1) {
            return [];
        }

        $sValue = reset($aFilterFieldData);

        return [
            'title' => $this->getFieldTitle(),
            'name' => $this->getFieldName(),
            'type' => self::getTypeWidget(),
            'value' => $sValue,
            'inputMaskAlias' => $this->getInputMaskOptions(),
        ];
    }

    public static function getTypeWidget()
    {
        return 'input';
    }

    public function canHaveTitle()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function filterInputVal($aDataItem)
    {
        // Не пропускаем пустые значения
        if (!$aDataItem[0]) {
            return false;
        }

        return true;
    }
}
