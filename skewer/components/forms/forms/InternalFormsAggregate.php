<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\ActiveRecord\ActiveRecord;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class InternalFormsAggregate.
 *
 * @property array $subForms
 */
abstract class InternalFormsAggregate extends Model
{
    private $_subForms = [];

    /**
     * Хранит список внутренних форм агрегатора,
     * а также список заменяемых полей внутренних форм
     *
     * @return array
     */
    abstract protected function getInternalForms(): array;

    abstract protected function save(): bool;

    abstract protected function delete(): bool;

    /**
     * @return null|ActiveRecord
     */
    abstract protected function getEntity();

    final public function getFullObject(): array
    {
        $fullObjects = $this->getAttributes();

        foreach ($this->subForms as $name => $object) {
            $fullObjects[$name] = $object->getBasicProperties();
        }

        return $fullObjects;
    }

    /**
     * @param array $values
     * @param bool $safeOnly
     */
    final public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        foreach ($this->subForms as $name => $object) {
            if (isset($values[$name])) {
                $object->setAttributes($values[$name], $safeOnly);
            }
        }
    }

    /**
     * @param null|array $attributeNames
     * @param bool $clearErrors
     *
     * @return bool
     */
    final public function validate(
        $attributeNames = null,
        $clearErrors = true
    ): bool {
        $parentNames = $attributeNames !== null
            ? array_filter((array) $attributeNames, 'is_string')
            : null;
        $success = parent::validate($parentNames, $clearErrors);

        /** @var InternalForm $object */
        foreach ($this->subForms as $name => $object) {
            if (is_array($object)) {
                $success = Model::validateMultiple($object) && $success;
            } else {
                $innerNames = $attributeNames !== null
                    ? ArrayHelper::getValue($attributeNames, $name)
                    : null;
                $success = $object->validate(
                    $innerNames ?: null,
                    $clearErrors
                ) && $success;
            }
        }

        return $success;
    }

    /**
     * @param null|string $attribute
     *
     * @return bool
     */
    public function hasErrors($attribute = null): bool
    {
        if ($attribute !== null) {
            return parent::hasErrors($attribute);
        }
        if (parent::hasErrors($attribute)) {
            return true;
        }

        foreach ($this->subForms as $object) {
            if (is_array($object)) {
                foreach ($object as $item) {
                    if ($item->hasErrors()) {
                        return true;
                    }
                }
            } else {
                if ($object->hasErrors()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getFirstErrors(): array
    {
        $errors = parent::getFirstErrors();
        foreach ($this->subForms as $name => $object) {
            if (is_array($object)) {
                foreach ($object as $i => $item) {
                    foreach ($item->getFirstErrors() as $attribute => $error) {
                        $errors[$name . '.' . $i . '.' . $attribute] = $error;
                    }
                }
            } else {
                foreach ($object->getFirstErrors() as $attribute => $error) {
                    $errors[$name . '.' . $attribute] = $error;
                }
            }
        }

        return $errors;
    }

    protected function getSubForms(): array
    {
        if ($this->_subForms) {
            return $this->_subForms;
        }

        foreach ($this->getInternalForms() as $name => $fields) {
            $method = 'get' . ucfirst($name);
            if (method_exists($this, $method)) {
                $object = $this->{$method}();
                assert($object instanceof InternalForm);
                $this->_subForms[$name] = $object;
            }
        }

        return $this->_subForms;
    }
}
