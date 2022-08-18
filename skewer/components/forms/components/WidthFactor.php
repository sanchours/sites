<?php

declare(strict_types=1);

namespace skewer\components\forms\components;

/**
 * Class WidthFactor
 * хранит основные функции по работе с множителем ширины поля.
 */
class WidthFactor
{
    public static function hasFactor(int $factor): bool
    {
        return isset(self::getFactorsWithTitle()[$factor]);
    }

    public static function getDefaultFactor(): int
    {
        return 1;
    }

    public static function getFactorsWithTitle(): array
    {
        return [
            1 => 'x1',
            2 => 'x2',
            3 => 'x3',
            4 => 'x4',
        ];
    }
}
