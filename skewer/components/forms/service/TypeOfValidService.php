<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\components\forms\components\typesOfValid\TypeOfValidAbstract;

class TypeOfValidService extends TypeServiceAbstract
{
    protected function getNamespaceComponent(): string
    {
        return 'skewer\\components\\forms\\components\\typesOfValid\\';
    }

    protected function getPathByComponents(): string
    {
        return RELEASEPATH . 'components/forms/components/typesOfValid/';
    }

    public function getTypesOfValidByPath(array $pathsTypeOfValid): array
    {
        foreach ($pathsTypeOfValid as $path) {
            $object = new $path();
            assert($object instanceof TypeOfValidAbstract);

            $types[$object->getName()] = $object->getTitle();
        }

        return $types ?? [];
    }

    public function getNameByPath(string $pathByClass): string
    {
        $object = new $pathByClass();
        assert($object instanceof TypeOfValidAbstract);

        return $object->getName();
    }
}
