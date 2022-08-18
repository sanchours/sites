<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

class Tel extends Date
{
    public function needAddRuleInValidation(): bool
    {
        return false;
    }
}
