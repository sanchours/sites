<?php

declare(strict_types=1);

namespace skewer\components\forms\entities;

use skewer\build\Adm\Order\ar\OrderRow;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\service\CrmFormService;
use yii\helpers\ArrayHelper;

/**
 * Class BuilderEntity построитель форм.
 * Формы укладываются рядом с модулем их использования.
 * Дополнительные перекрывающие шаблоны укладываюся рядом
 *
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
abstract class BuilderEntity
{
    /** @var string $cmd обработчик данных из формы */
    public $cmd;

    /** @var string $addClass дополнительный класс по обработке */
    public $addClass = '';
    /** @var string $method метод обработки */
    public $method = 'post';
    /** @var string $moduleName модуль обработки */
    public $moduleName = 'Forms';

    /**
     * @var array Поля формы
     */
    protected static $fieldsForCreatedForm = [];

    private $_innerData = [];

    /** @var CrmFormService */
    private $_serviceFormCRM;

    /** @var array Ошибки при валидации формы */
    private $errors = [];

    /** @var bool Осуществлялась ли отправка данных в CRM */
    protected $isSendCRMData = false;
    /** @var string Ключ для подтверждения редиректа */
    public $redirectKeyName = 'default';

    public function __construct(array $innerData = [], array $config = [])
    {
        $this->setFormAggregate();
        $this->_innerData = $innerData;
        $this->setValues();
        $this->addClass = $this->getFormAggregate()->getSettings()->class ?? '';

        $this->_serviceFormCRM = new CrmFormService();
    }

    //abstract public function deleteField();

    /**
     * Внутри этой функции вызываем создание полей.
     */
    abstract public static function createTable();

    /**
     * Имя формы.
     *
     * @return string
     */
    abstract public static function tableName(): string;

    /**
     * Установка дополнительных параметров,
     * которые нужны для конкретной сущности.
     *
     * @param TemplateForm $templateForm
     *
     * @return mixed
     */
    abstract public function setAddParamsForShowForm(TemplateForm &$templateForm);

    /**
     * обязательное сохранение в случае передачи данных.
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->isSendCRMData) {
            $this->sendDataToCRM();
            $this->isSendCRMData = true;
        }

        return true;
    }

    /**
     * Ссылка на расположение настройк текста автоответа.
     *
     * @return string
     */
    public function getLinkAutoReply(): string
    {
        return '';
    }

    /**
     * Передача OrderRow приводит к отправке дополнительных данных в CRM.
     *
     * @param null|OrderRow $orderRow
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    protected function sendDataToCRM(OrderRow $orderRow = null)
    {
        $orderId = ($orderRow instanceof OrderRow) ? $orderRow->id : null;
        if ($this->formAggregate->settings->crm) {
            $this->_serviceFormCRM->send2Crm($this, $orderId);
        }
    }

    /**
     * @param int $idForm
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    final protected static function createFields(int $idForm)
    {
        foreach (static::$fieldsForCreatedForm as $field) {
            $fieldAggregate = new FieldAggregate($idForm);

            if (isset($field['type']['name'])) {
                $fieldAggregate->setTypeFieldByPath($field['type']['name']);
                unset($field['type']['name']);
            }

            if (isset($field['type']['typeOfValid'])) {
                $fieldAggregate->setTypeOfValidByPath($field['type']['typeOfValid']);
                unset($field['type']['typeOfValid']);
            }

            $fieldAggregate->setAttributes($field);
            if (!$fieldAggregate->save()) {
                throw new \Exception(current($fieldAggregate->getFirstErrors()));
            }
        }

        FormEntity::updateEntity($idForm);
    }

    public function setFormAggregate()
    {
        $formEntity = FormEntity::getBySlug(static::tableName());
        assert($formEntity instanceof FormEntity);
        $this->formAggregate = new FormAggregate($formEntity->id);
    }

    /**
     * @return FormAggregate
     */
    public function getFormAggregate(): FormAggregate
    {
        if (empty($this->formAggregate)) {
            $this->setFormAggregate();
        }

        return $this->formAggregate;
    }

    /**
     * @throws \ReflectionException
     *
     * @return string
     */
    public function getDirPath()
    {
        $class = new \ReflectionClass(static::class);

        return dirname($class->getFileName());
    }

    public function getField(string $name)
    {
        $fields = $this->fields;
        if (isset($fields[$name])) {
            return $fields[$name];
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasFieldsError(): bool
    {
        /** @var FieldAggregate $field */
        foreach ($this->fields as $field) {
            if ($field->getFirstErrors()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     *
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return bool|mixed
     */
    final public function __get($name)
    {
        $methodGet = 'get' . ucfirst($name);
        if (method_exists($this, $methodGet)) {
            return $this->{$methodGet}();
        }

        if (
            $this->hasFieldInDoc($name)
            && $this->getField($name)
        ) {
            return $this->getField($name)->value;
        }
        $this->setError(\Yii::t('auth', 'not_field_form') . " {$name}");

        return false;
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    final public function __set($name, $value)
    {
        $methodSet = 'set' . ucfirst($name);
        if (method_exists($this, $methodSet)) {
            $this->{$methodSet}($value);
        } elseif (
            $this->hasFieldInDoc($name)
            && $this->getField($name)) {
            $this->getField($name)->value = $value;
        } else {
            $this->setError(\Yii::t('auth', 'not_field_form') . " {$name}");
        }
    }

    /**
     * @param $name
     *
     * @throws \ReflectionException
     *
     * @return bool
     */
    private function hasFieldInDoc($name): bool
    {
        $oRefl = new \ReflectionClass($this);
        $sDocClass = $oRefl->getDocComment();
        $subStr = mb_strstr($sDocClass, '$' . $name);

        return ($subStr) ? true : false;
    }

    /**
     * @param string $formHash
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function validate(string $formHash)
    {
        if ($this->formAggregate->validateInnerData(
            $this->_innerData,
            $formHash
        )) {
            $success = true;

            foreach ($this->fields as &$field) {
                if ($field->type->isPhoneValidate()) {
                    $field->type->setTypeOfValid('text');
                }

                $success = $success && $field->validateValue($this->formAggregate->handler->getTypeObject());
            }

            return $success;
        }

        return false;
    }

    /**
     * Установака значения для полей, исходя из переданных параметров.
     */
    public function setValues()
    {
        /** @var FieldAggregate $field */
        foreach ($this->fields as $field) {
            if (empty($this->_innerData)) {
                $value = $field->type->default;
            } else {
                $value = ArrayHelper::getValue(
                    $this->_innerData,
                    $field->settings->slug,
                    ''
                );
            }

            $field->value = $value;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setError(string $errForm)
    {
        $this->errors[] = $errForm;
    }

    public function getInnerParamByName(
        string $name,
        string $default = ''
    ): string {
        $paramValue = ArrayHelper::getValue($this->_innerData, $name, '');
        if (is_array($paramValue)) {
            $paramValue = implode(', ', $paramValue);
        }
        return $paramValue;
    }

    public function getInnerParams(): array
    {
        return $this->_innerData;
    }

    /**
     * Получение переданных значений в соответствии с сохраняемым типов БД.
     *
     * @return array
     */
    public function getValues(): array
    {
        $valuesForInsertDB = [];
        foreach ($this->fields as $key => $field) {
            if ($field->type->getFieldObject()->getTypeDB() !== null) {
                $valuesForInsertDB[$key] = $field->type->getFieldObject()->getValueForDB($field->value);
            }
        }

        return $valuesForInsertDB;
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return array
     */
    public function getFields(): array
    {
        if (empty($this->fields)) {
            $this->setFields();
        }

        return $this->fields;
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function setFields()
    {
        // класс для сетки
        $sum = 1;
        $fields = array_values($this->formAggregate->getFields());
        $fieldsCount = count($fields);

        $fieldsChanged = [];
        /** @var FieldAggregate $field */
        foreach ($fields as $key => $field) {
            if ($field->settings->newLine || $key == 0) {
                $i = $key + 1;
                $sum = $field->settings->widthFactor;

                // Здесь высчитывается максимальное число полей формы,
                // расположенных на одной строке, на базе чего формируется имя css-класса
                while ($i < $fieldsCount) {
                    if ($fields[$i]->settings->newLine) {
                        break;
                    }
                    $sum += $fields[$i]->settings->widthFactor;
                    ++$i;
                }

                if ($sum > 5) {
                    $sum = 5;
                }
            }

            $field->labelClass = ($sum > 1) ? $field->settings->widthFactor . '-' . $sum : '1';
            $field->settings->title = \Yii::tSingleString($field->settings->title);
            $fieldsChanged[$field->settings->slug] = $field;
        }

        $this->fields = $fieldsChanged;
    }

    public function hasSendData(): bool
    {
        return !empty($this->_innerData);
    }

    /**
     * Получение наименования класса
     * для поиска перекрытых шаблонов для button и input.
     *
     * @param string $shortNameTemplate
     *
     * @return string
     */
    public function getNameClassForTemplate(string $shortNameTemplate): string
    {
        $classes = explode('\\', get_class($this));

        return array_pop($classes) . ucfirst($shortNameTemplate);
    }
}
