<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\forms\components\dto\FieldFormBuilderByType;

class Rating extends Radio
{
    protected $typeExtJs = '';
    protected $typeDB = 'int';
    protected $lengthValueDB = 5;
    protected $isEditSizeDB = false;

    public function changeDefaultValueForLetter(
        string &$defaultValue,
        string &$value,
        array $defaultValues
    ) {
    }

    public function getTrueValue($sValue, $sParamDef)
    {
        return $sValue;
    }

    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
        $form->fieldSelect(
            $fieldFormBuilder->slug,
            $fieldFormBuilder->title,
            [0, 1, 2, 3, 4, 5],
            [],
            false
        );
    }

    public function getValueForDB(string $value)
    {
        return (int) $value;
    }
}
