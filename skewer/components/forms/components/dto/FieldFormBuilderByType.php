<?php

declare(strict_types=1);

namespace skewer\components\forms\components\dto;

use skewer\components\forms\components\fields\TypeFieldAbstract;

/**
 * Class FieldFormBuilderByType
 * предназначен для передачи параметров в интерфейс "Заказы из форм" в админке.
 */
class FieldFormBuilderByType
{
    /** @var int $id */
    public $id;

    /** @var string $title */
    public $title;

    /** @var string $slug */
    public $slug;
    /** @var null|string $formParam */
    public $formParam;
    /** @var array $defaultValues */
    public $defaultValues;

    /** @var TypeFieldAbstract */
    public $fieldObject;
}
