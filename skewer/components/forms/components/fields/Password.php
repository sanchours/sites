<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

class Password extends Input
{
    public function getParseData4CRM(string $title, string $value): string
    {
        return '';
    }
}
