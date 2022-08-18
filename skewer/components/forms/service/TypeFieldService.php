<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\components\forms\components\fields\TypeFieldAbstract;

class TypeFieldService extends TypeServiceAbstract
{
    protected function getNamespaceComponent(): string
    {
        return 'skewer\\components\\forms\\components\\fields\\';
    }

    protected function getPathByComponents(): string
    {
        return RELEASEPATH . 'components/forms/components/fields/';
    }

    public function getNameByPath(string $pathByClass): string
    {
        $object = new $pathByClass();
        assert($object instanceof TypeFieldAbstract);

        return $object->getName();
    }
}
