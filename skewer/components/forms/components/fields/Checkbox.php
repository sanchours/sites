<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

class Checkbox extends TypeFieldAbstract
{
    protected $typeExtJs = 'check';
    protected $typeDB = 'int';
    protected $lengthValueDB = 1;
    protected $isEditSizeDB = false;

    public function setFieldValue(string $name, string $value): string
    {
        return $value ? '1' : '0';
    }

    public function getValueForDB(string $value)
    {
        return (int) $value;
    }

    public function getTrueValue($sValue, $sParamDef)
    {
        return $sValue ? \Yii::t('forms', 'yes') : \Yii::t('forms', 'no');
    }
}
