<?php

declare(strict_types=1);

namespace skewer\components\forms\components\dto;

/**
 * Class FieldForShowObject
 * хранит параметры для вывода в шаблонах типов полей.
 */
class FieldForShowObject
{
    public $paramId;
    public $paramName;
    public $classModify;

    private $_title;
    private $_value;
    private $_checked;

    public function __construct(string $title, string $value, bool $checked)
    {
        $this->_title = trim($title);
        $this->_value = trim($value);
        $this->_checked = $checked;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->_title;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->_value;
    }

    /**
     * @return bool
     */
    public function getChecked(): bool
    {
        return $this->_checked;
    }
}
