<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\forms\components\dto\FieldFormBuilderByType;

class Radio extends Select
{
    protected $typeExtJs = 'radio';

    public function parse(
        string $defaultValue,
        array $variants = [],
        array $presetValues = []
    ): array {
        return $variants;
    }

    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
        $form->fieldSelect(
            $fieldFormBuilder->slug,
            $fieldFormBuilder->title,
            $fieldFormBuilder->defaultValues
        );
    }
}
