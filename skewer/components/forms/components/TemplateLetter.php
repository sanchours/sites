<?php

declare(strict_types=1);

namespace skewer\components\forms\components;

use skewer\base\site_module\Parser;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;

/**
 * Class TemplateLetter
 * хранение параметров для формирования письм
 *
 * @property string[] $fields
 */
class TemplateLetter
{
    /** @var string */
    private $formTitle;
    /** @var bool */
    private $sendDataFromForm;

    /** @var bool */
    public $tableHide = false;

    /** @var string[] Готовые шаблоны для вставки в письмо */
    private $_fields;

    public function __construct(FormAggregate $formAggregate, array $fields)
    {
        $this->formTitle = $formAggregate->settings->title;
        $this->sendDataFromForm = !$formAggregate->settings->noSendDataInLetter;
        $this->setFields($fields);
    }

    /**
     * @return string
     */
    public function getFormTitle(): string
    {
        return $this->formTitle;
    }

    public function getSendDataFromForm(): bool
    {
        return $this->sendDataFromForm;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->_fields;
    }

    /**
     * Получить распарсенные значения полей param_value - пришедшее значение.
     *
     * @param FieldAggregate[] $fields
     */
    public function setFields(array $fields)
    {
        $pathTemplateLetter = RELEASEPATH . 'components/forms/templates/el4Letter';

        foreach ($fields as $field) {
            $defaultValues = $field->parseDataAsList($field->type->default);
            $default = $field->type->default;
            $value = $field->value;
            $field->type->getFieldObject()->changeDefaultValueForLetter(
                $default,
                $value,
                $defaultValues
            );
            $field->type->setDefault($default);
            $field->value = $value;

            $templateField = mb_strtolower($field->type->name) . '.twig';

            $template = file_exists("{$pathTemplateLetter}/{$templateField}")
                ? $templateField
                : 'prototype.twig';

            $this->_fields[] = Parser::parseTwig(
                $template,
                ['field' => $field],
                $pathTemplateLetter
            );
        }
    }

    /**
     * @param string $introduction Заголовок письма
     * @param string $showLinkAdd Ссылка на административное отображение формы
     *
     * @return string
     */
    public function getBodyForLetter(
        string $introduction,
        string $showLinkAdd
    ): string {
        return Parser::parseTwig('letter.twig', [
            'templateLetter' => $this,
            'introduction' => $introduction,
            'showLinkAdd' => $showLinkAdd,
        ], RELEASEPATH . 'components/forms/templates');
    }
}
