<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\forms\components\dto\FieldFormBuilderByType;
use skewer\components\forms\components\typesOfValid\Text;

class Delimiter extends TypeFieldAbstract
{
    protected $typeExtJs = 'hide';
    protected $lengthValueDB = 0;
    protected $isEditSizeDB = false;
    protected $typeDB;

    public function getParseData4CRM(string $title, string $value): string
    {
        return '';
    }

    protected function getAvailableTypesOfValid(): array
    {
        return [Text::class];
    }

    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
    }
}
