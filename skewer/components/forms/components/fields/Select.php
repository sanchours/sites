<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\catalog\Dict;
use skewer\components\forms\ApiField;
use skewer\components\forms\components\dto\FieldFormBuilderByType;
use yii\helpers\ArrayHelper;

class Select extends TypeFieldAbstract
{
    protected $typeExtJs = 'select';

    /**
     * @param string $defaultValue
     * @param array $variants
     * @param array $presetValues
     *
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return array
     */
    public function parse(
        string $defaultValue,
        array $variants = [],
        array $presetValues = []
    ): array {

        $aDictVariants = $this->getVariantsFromDictionary($defaultValue);
        if ($aDictVariants) {
            return $aDictVariants;
        }

        $classAndMethod = explode('.', $defaultValue);
        if (!isset($classAndMethod[1]) && !empty($variants)) {
            return $variants;
        }

        $variants = $this->getVariantsFromDefaultByClass($defaultValue);
        if ($variants === null) {
            $variants = $presetValues;
        }

        return $variants;
    }

    /**
     * Получение вариантов отображения по методу класса.
     *
     * @param string $defaultValue
     *
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return null|mixed
     */
    public function getVariantsFromDefaultByClass(string $defaultValue)
    {
        if (preg_match(
            '/^([a-zA-Z0-9\\\\]+)\.(\w+)\(\)$/',
            $defaultValue,
            $params
        )
        ) {
            $className = $params[1] ?? null;
            $method = $params[2] ?? null;

            $class = new \ReflectionClass($className);

            if (!($class instanceof \ReflectionClass)) {
                throw new \Exception("Не создан класс [{$className}]");
            }

            if ($class->getParentClass()->name != 'skewer\base\site\ServicePrototype') {
                throw new \Exception('Попытка запуска неразрешенного класса');
            }

            $objectCurrentClass = new $className();

            if (!method_exists($objectCurrentClass, $method)) {
                throw new \Exception('Попытка запуска несуществующего метода');
            }

            return call_user_func_array([$objectCurrentClass, $method], []);
        }
    }

    public function getVariantsFromDictionary(string $defaultValue) : array
    {
        $aDataForSelect = [];
        $sDictName = $this->extractDictFromString($defaultValue);

        if (isset($sDictName)) {
            $aDictData = Dict::getDictByName($sDictName);
            $aDataForSelect = ArrayHelper::index($aDictData, 'alias');
            $aDataForSelect = ArrayHelper::getColumn($aDataForSelect, 'title');
        }
        return $aDataForSelect;
    }

    /**
     * @param FormBuilder $form
     * @param FieldFormBuilderByType $fieldFormBuilder
     *
     * @throws \yii\base\UserException
     */
    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
        $defaultValues = $fieldFormBuilder->defaultValues;
        $first = $defaultValues ? array_shift($defaultValues) : '';
        $sDictName = $this->extractDictFromString($first);

        if ($sDictName) {
            $form->fieldSelect(
                $fieldFormBuilder->slug,
                $fieldFormBuilder->title,
                $this->parseDictionary($sDictName)
            );
        } else {
            $form->fieldSelect(
                $fieldFormBuilder->slug,
                $fieldFormBuilder->title,
                $fieldFormBuilder->defaultValues
            );
        }
    }

    /**
     * Формирует массив для списка из словаря.
     *
     * @param $sNameDict
     *
     * @throws \yii\base\UserException
     *
     * @return array
     */
    protected function parseDictionary($sNameDict)
    {
        $aDict = Dict::getDictByName($sNameDict);
        $aItems = [];
        if ($aDict) {
            foreach ($aDict as $iKey => $aValueDict) {
                $aItems[$aValueDict['alias']] = $aValueDict['title'];
            }
        }

        return $aItems;
    }

    /**
     * Метод для получения названия словря из строки.
     * @param string $string
     * @return string
     */
    protected function extractDictFromString(string $string): string
    {
        if (!$string) {
            return '';
        }

        preg_match('/^dictionary\(([^&]*)\)$/i', $string, $aDictMatches);

        return $aDictMatches[1] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function changeDefaultValueForLetter(
        string &$defaultValue,
        string &$value,
        array $defaultValues
    ) {
        if (isset($defaultValues[$defaultValue])) {
            $defaultValue = $value = $defaultValues[$defaultValue];
        }
    }

    public function getTrueValue($sValue, $sParamDef)
    {
        $sValue = ApiField::getValueByParamDefault($sValue, $sParamDef);

        return $sValue;
    }
}
