<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

class Url extends TypeOfValidAbstract
{
    public function validate(int $minLength, int $maxLength, string $value): bool
    {
        return
            filter_var($value, FILTER_VALIDATE_URL) !== false
            && mb_strlen($value, 'UTF-8') >= $minLength
            && mb_strlen($value, 'UTF-8') <= $maxLength;
    }
}
