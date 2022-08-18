<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

use skewer\components\forms\components\TypeObjectInterface;

abstract class TypeOfValidAbstract implements TypeObjectInterface
{
    abstract public function validate(
        int $minLength,
        int $maxLength,
        string $value
    ): bool;

    final public function getTitle(): string
    {
        return \Yii::t('forms', 'validation_' . lcfirst($this->getName()));
    }

    final public function getName(): string
    {
        $paramsPath = explode('\\', get_class($this));

        return $paramsPath[count($paramsPath) - 1];
    }

    public function needAddRuleInValidation(): bool
    {
        return true;
    }
}
