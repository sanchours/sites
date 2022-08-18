<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\forms\ApiField;
use skewer\components\forms\components\dto\FieldFormBuilderByType;

class CheckboxGroup extends Select
{
    protected $typeExtJs = 'multiselect';

    public function parse(
        string $defaultValue,
        array $variants = [],
        array $presetValues = []
    ): array {
        $aDictVariants = $this->getVariantsFromDictionary($defaultValue);
        return $aDictVariants ?: $variants;
    }

    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
        $aValues = $this->getDictOrDefaultValues($fieldFormBuilder->defaultValues);
        $form->fieldMultiSelect(
            $fieldFormBuilder->slug,
            $fieldFormBuilder->title,
            $aValues
        );
    }

    public function getValueForLetter($sParamValue, $sParamDefault)
    {
        return (mb_strpos($sParamDefault, ':') !== false)
            ? ApiField::getValueByParamDefault($sParamValue, $sParamDefault)
            : $sParamValue;
    }

    public function changeDefaultValueForLetter(
        string &$defaultValue,
        string &$value,
        array $defaultValues
    ) {
        $valuesFromPost = explode(',', $value);
        $resultTitle = [];
        foreach ($valuesFromPost as $valuePost) {
            if (isset($defaultValues[$valuePost])) {
                $resultTitle[] = $defaultValues[$valuePost];
            }
        }
        $defaultValue = $value = implode(',', $resultTitle);
    }

    public function getTrueValue($sValue, $sParamDef)
    {
        return $sValue;
    }

    /**
     * Метод определяет откуда надо брать значения для построения интерфейса.
     * @param array $aDefaultValues
     * @return array
     * @throws \yii\base\UserException
     */
    private function getDictOrDefaultValues(array $aDefaultValues): array
    {
        $sFirstVal = $aDefaultValues ? array_shift($aDefaultValues) : '';
        $sDictName = $this->extractDictFromString($sFirstVal);

        if (!$sDictName) {
            return $aDefaultValues;
        }

        return $this->parseDictionary($sDictName);
    }
}
