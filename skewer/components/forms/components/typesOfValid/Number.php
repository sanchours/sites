<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

class Number extends TypeOfValidAbstract
{
    public function validate(int $minLength, int $maxLength, string $value): bool
    {
        $match = preg_match('/^[\\-]?\\d+[\\.]?\\d*$/', $value);

        return $match === 1;
    }
}
