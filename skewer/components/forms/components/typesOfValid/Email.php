<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

use skewer\helpers\Validator;

class Email extends TypeOfValidAbstract
{
    public function validate(int $minLength, int $maxLength, string $value): bool
    {
        return Validator::isEmail($value);
    }
}
