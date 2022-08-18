<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

class Digits extends TypeOfValidAbstract
{
    public function validate(int $minLength, int $maxLength, string $value): bool
    {
        $match = preg_match("/^\\d{{$minLength},{$maxLength}}$/", $value);

        return $match === 1;
    }
}
