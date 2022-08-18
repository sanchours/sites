<?php

declare(strict_types=1);

namespace skewer\components\forms\components;

/**
 * Interface TypeObjectInterface
 * общий интерфейс для получение технического имени и заголовка
 * типа поля и типа валидации поля.
 */
interface TypeObjectInterface
{
    public function getTitle(): string;

    public function getName(): string;
}
