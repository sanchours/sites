<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\base\ui\ARSaveException;
use skewer\components\forms\components\dto\FieldForShowObject;
use skewer\components\forms\components\fields\TypeFieldAbstract;
use skewer\components\forms\components\handlerType\Prototype;
use skewer\components\forms\components\typesOfValid\TypeOfValidAbstract;
use skewer\components\forms\entities\FieldEntity;
use yii\base\UserException;

/**
 * Class FieldAggregate.
 *
 * @property SettingsFieldForm $settings
 * @property TypeFieldForm $type
 * @property FieldEntity $entity
 */
class FieldAggregate extends InternalFormsAggregate
{
    public $idField;
    public $idForm;

    private $value;
    /** @var string Виртуальное свойства для шаблона */
    public $labelClass;

    /** @var null|FieldEntity */
    private $_fieldEntity;

    /** @var SettingsFieldForm $_settings */
    private $_settings;

    /** @var TypeFieldForm $_type */
    private $_type;

    /**
     * FieldAggregate constructor.
     *
     * @param int $idForm
     * @param null|int $idField
     * @param array $config
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function __construct(
        int $idForm,
        int $idField = null,
        array $config = []
    ) {
        $this->idField = $idField;
        $this->idForm = $idForm;

        $this->setEntity();

        parent::__construct($config);
    }

    protected function getInternalForms(): array
    {
        return [
            $this->settings->getShortNameObject() => [],
            $this->type->getShortNameObject() => [
                'name' => 'type',
                'title' => '',
            ],
        ];
    }

    public function rules()
    {
        return [
            [['idField', 'idForm'], 'integer'],
        ];
    }

    /**
     * @param null $entity
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function setEntity($entity = null)
    {
        if ($entity instanceof FieldEntity) {
            $this->_fieldEntity = $entity;
            $this->idField = $entity->id;
        } else {
            $this->_fieldEntity = $this->idField
                ? FieldEntity::getById($this->idField)
                : new FieldEntity();
        }

        if ($this->idField) {
            $attributes = $this->_fieldEntity->getAttributes();
            $this->setSettings($attributes);
            $this->setType();
        }
    }

    public function getEntity()
    {
        return $this->_fieldEntity;
    }

    /**
     * @throws ARSaveException
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            throw new UserException(current($this->getFirstErrors()));
        }

        if ($this->_fieldEntity === null) {
            $this->setEntity();
        }

        $this->_fieldEntity->form_id = $this->idForm;

        foreach ($this->getInternalForms() as $formName => $fields) {
            $this->_fieldEntity->setAttributes(
                $this->{$formName}->getBasicPropertiesForEntity(),
                true,
                $fields
            );
        }

        if ($this->_fieldEntity->validate() === false) {
            throw new ARSaveException($this->_fieldEntity);
        }

        return $this->_fieldEntity->save();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->type->deleteExtraData($this->idForm, $this->idField);

        return $this->_fieldEntity instanceof FieldEntity
            ? (bool) $this->_fieldEntity->delete()
            : false;
    }

    public function clearExtraData()
    {
        $this->type->deleteExtraData($this->idForm, $this->idField);
        $this->type->getFieldObject()->clearExtraData(
            $this->idForm,
            $this->settings->slug
        );
    }

    private function setSettings(array $attributes = [])
    {
        $this->_settings = new SettingsFieldForm($attributes);
    }

    public function getSettings(): SettingsFieldForm
    {
        if (!isset($this->_settings)) {
            if ($this->_fieldEntity->id) {
                $this->setSettings($this->_fieldEntity->getAttributes());
            } else {
                $this->setSettings([
                    'title' => \Yii::t('forms', 'new_param'),
                    'newLine' => true,
                ]);
            }
        }

        return $this->_settings;
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     */
    private function setType()
    {
        if ($this->_fieldEntity->id) {
            $this->_type = new TypeFieldForm(
                $this->_fieldEntity->type,
                $this->_fieldEntity->type_of_valid
            );
            $attributes = $this->_fieldEntity->getAttributes();
            $attributesForChange = $this->getInternalForms()[$this->type->getShortNameObject()];
            if ($attributesForChange) {
                foreach ($attributes  as $name => $value) {
                    if (isset($attributesForChange[$name])) {
                        unset($attributes[$name]);
                        $attributes[$this->getInternalForms()[$this->type->getShortNameObject()][$name]] = $value;
                    }
                }
            }
            $this->_type->setAttributes($attributes);
        } else {
            $this->_type = new TypeFieldForm();
            $this->_type->setMaxLength();
            $this->_type->setDisplayType();
        }
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return TypeFieldForm
     */
    public function getType(): TypeFieldForm
    {
        if (!isset($this->_type)) {
            $this->setType();
        }

        return $this->_type;
    }

    public function hasChangedType()
    {
        return $this->_fieldEntity->isAttributeChanged('type');
    }

    /**
     * Формирование массива из значений для парсинга в шаблон определенного типа поля.
     *
     * @param string $presetValue
     *
     * @return array
     */
    public function getViewItems(string $presetValue = '')
    {
        if (empty($presetValue)) {
            $presetValue = $this->value;
        }
        $variants = $this->type->getFieldObject()->parse(
            $this->type->default,
            $this->parseDataAsList($this->type->default),
            $this->parseDataAsList($presetValue)
        );

        $variantsForShow = $this->getFullArray4Parse($variants, $presetValue);

        return $variantsForShow;
    }

    /**
     * Формирование полноценного массива для парсинга в шаблон.
     *
     * @param $variants
     * @param $presetValue
     *
     * @return array
     */
    private function getFullArray4Parse(
        array $variants,
        string $presetValue
    ): array {
        $fields = [];

        //тут массив
        $presetValues = explode(',', $presetValue);

        if (count($presetValues) === 1) {
            if (empty($presetValue) || !(isset($variants[$presetValue]))) {
                //тут строка
                $presetValue = current($presetValues);
            }
        } else {
            $presetValue = $presetValues;
        }

        $indexForInstall = 0;

        $notArrayPresetData = !is_array($presetValue);
        foreach ($variants as $value => $title) {
            $checked = $notArrayPresetData
                ? (string) $value === $presetValue
                : in_array($value, $presetValue);

            $field = new FieldForShowObject($title, (string) $value, $checked);

            $field->paramId = $this->settings->slug . $indexForInstall++;
            $field->paramName = $this->settings->slug;
            $field->classModify = $this->settings->classModify;

            $fields[] = $field;
        }

        return $fields;
    }

    public function setValue($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $this->value = $this->type->getFieldObject()->setFieldValue(
            $this->settings->slug,
            $value
        );
    }

    /**
     * @return string| null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Формирование массива [value=>title] из
     * входящих строк вида "value:title".
     *
     * @param string $variantsInOneString
     *
     * @return array
     */
    final public function parseDataAsList(string $variantsInOneString): array
    {
        $variants = explode(
            ';',
            trim(
                preg_replace(
                    '/\x0a+|\x0d+/Uims',
                    '',
                    $variantsInOneString
                )
        )
        );

        $variants = array_diff($variants, ['']);
        $aItems = [];

        foreach ($variants as $variant) {
            if (mb_strpos($variant, ':')) {
                list($value, $title) = explode(':', $variant);
            } else {
                $value = $title = $variant;
            }

            $aItems[$value] = $title;
        }

        return $aItems;
    }

    /**
     * Установка типа поля поля по классу.
     *
     * @param string $pathByClass
     */
    public function setTypeFieldByPath(string $pathByClass)
    {
        $object = new $pathByClass();
        assert($object instanceof TypeFieldAbstract);

        $this->type->name = $object->getName();
    }

    /**
     * Установка типа валидации поля по классу.
     *
     * @param string $pathByClass
     */
    public function setTypeOfValidByPath(string $pathByClass)
    {
        $object = new $pathByClass();
        assert($object instanceof TypeOfValidAbstract);

        $this->type->typeOfValid = $object->getName();
    }

    /**
     * Получение правил валидации по типу поля.
     *
     * @return array
     */
    public function getValidateRules(): array
    {
        return $this->type->getFieldObject()->getValidateRules(
            $this->type->maxLength
        );
    }

    /**
     * Используется в шаблоне для группировки полей.
     *
     * @return int
     */
    public function getGroup()
    {
        return $this->settings->groupPrevField;
    }

    /**
     * Получение шаблона по типу.
     * Используется в шаблоне форм
     *
     * @return string
     */
    public function getTypeTemplate()
    {
        $path = '/fields/';

        if ($this->type->displayType) {
            return "{$path}{$this->type->name}/type{$this->type->displayType}.twig";
        }

        return "{$path}{$this->type->name}.twig";
    }

    public function getClassVal()
    {
        return isset($this->labelClass) ? $this->labelClass : '1';
    }

    public function getError()
    {
        return parent::getFirstErrors();
    }

    /**
     * Отдает true если нужно показать стандартный placeholder.
     */
    public function showDefaultPlaceholder(): bool
    {
        $param_man_params = str_replace(
            'data-hide-placeholder',
            '',
            $this->settings->specStyle
        );

        return
            !preg_match('/\bplaceholder=[\'"]/', $param_man_params)
            &&
            $this->settings->labelPosition == SettingsFieldForm::LABEL_POSITION_NONE;
    }

    /**
     * @param Prototype $handlerType
     *
     * @return bool
     */
    public function validateValue(Prototype $handlerType)
    {
        if (is_array($this->value)) {
            return false;
        }

        /* проверка на заполнение обязательных полей */
        if ($this->value !== null && trim($this->value) === '') {
            if ($this->settings->required) {
                $this->addError(
                    $this->settings->slug,
                    \Yii::t('forms', 'err_empty_field')
                );

                return false;
            }

            return true;
        }

        try {
            $fieldTypeObject = $this->type->getFieldObject();
            if (
                $this->type->maxLength === 0
                || $this->type->getTypeOfValidObject()->validate(
                        1,
                        $this->type->maxLength,
                        $this->value
                )
            ) {
                if ($fieldTypeObject->needSaveFile) {
                    $this->value = $handlerType->saveFiles($this);
                }

                return true;
            }
        } catch (\Exception $e) {
            $this->addError($this->settings->slug, $e->getMessage());
        }

        $this->addError(
            'ans_validation',
            rtrim(\Yii::t('forms', 'ans_validation'), '!')
        );

        return false;
    }

    public function getPathToFormFiles($bOnlyForm = false): string
    {
        $path = "uploads/{$this->idForm}/";

        return $bOnlyForm ? $path : $path . "{$this->idField}/";
    }
}
