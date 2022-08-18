<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\forms\components\TemplateForm;
use skewer\components\sluggable\Inflector;
use yii\base\Model;

/**
 * Class InternalForm
 * предназначен для формирования внутренних форм агрегатора с дополнительной бизнес-логикой.
 */
class InternalForm extends Model
{
    /**
     * Получение базовых свойств объекта.
     *
     * @return array
     */
    final public function getBasicProperties(): array
    {
        $baseProperties = [];
        foreach ($this->safeAttributes() as $name) {
            $baseProperties[$name] = $this->{$name};
        }

        return $baseProperties;
    }

    /**
     * Короткое наименование формы,
     * которое будет использоваться в агрегаторе.
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    public function getShortNameObject(): string
    {
        return mb_strtolower(str_replace(
            'Form',
            '',
            (new \ReflectionClass($this))->getShortName()
        ));
    }

    /**
     * Получение базовых свойств объекта для модельки.
     *
     * @return array
     */
    public function getBasicPropertiesForEntity(): array
    {
        $baseProperties = [];

        foreach ($this->safeAttributes() as $name) {
            $nameAttr = Inflector::camel2id($name, '_');
            $baseProperties[$nameAttr] = $this->{$name};
        }

        return $baseProperties;
    }

    /**
     * Параметры формы для передачи на клиентскую часть в шаблон.
     *
     * @param string $formHash
     * @param TemplateForm $template
     * @param bool $requiredFields
     */
    public function setFormDisplayOptions(
        string $formHash,
        TemplateForm &$template,
        bool $requiredFields = false
    ) {
    }

    /**
     * Валидация входных параметров.
     *
     * @param array $innerData
     * @param string $formHash
     *
     * @return bool
     */
    public function validateInnerData(array $innerData, string $formHash): bool
    {
        return true;
    }
}
