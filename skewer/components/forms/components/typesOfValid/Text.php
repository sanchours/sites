<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

class Text extends TypeOfValidAbstract
{
    public function validate(int $minLength, int $maxLength, string $value): bool
    {
        $value = str_replace("\r", '', $value);
        $value = str_replace("\n", '', $value);
        $value = str_replace("\t", '', $value);

        return
            mb_strlen($value, 'UTF-8') >= $minLength
            && mb_strlen($value, 'UTF-8') <= $maxLength;
    }

    public function needAddRuleInValidation(): bool
    {
        return false;
    }
}
