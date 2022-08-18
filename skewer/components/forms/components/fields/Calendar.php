<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\forms\components\dto\FieldFormBuilderByType;

class Calendar extends Select
{
    protected $typeExtJs = 'date';
    protected $typeDB = 'date';
    protected $lengthValueDB = 0;
    protected $isEditSizeDB = false;

    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
        $form->field(
            $fieldFormBuilder->slug,
            $fieldFormBuilder->title,
            $this->typeExtJs
        );
    }

    public function getTrueValue($sValue, $sParamDef)
    {
        return $sValue;
    }

    public function getValidateRules(int $maxLength): array
    {
        return [];
    }

    public function getValueForDB(string $value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : '';
    }
}
