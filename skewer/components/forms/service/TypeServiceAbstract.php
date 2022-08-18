<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\components\forms\components\fields\TypeFieldAbstract;
use skewer\components\forms\components\typesOfValid\TypeOfValidAbstract;

abstract class TypeServiceAbstract
{
    abstract protected function getNamespaceComponent(): string;

    abstract protected function getPathByComponents(): string;

    abstract protected function getNameByPath(string $pathByClass): string;

    final public function getPathsToObjects()
    {
        $objectTypes = [];
        /** @var TypeFieldAbstract|TypeFieldAbstract|TypeOfValidAbstract $type */
        foreach ($this->getTypesObject() as $type) {
            $objectTypes[] = get_class($type);
        }

        return $objectTypes;
    }

    /**
     * @param string $type
     *
     * @throws \ReflectionException
     *
     * @return bool
     */
    final public function hasType(string $type): bool
    {
        $namespaceClass = $this->getNamespaceComponent() . "{$type}";
        if (class_exists($namespaceClass)) {
            $reflection = new \ReflectionClass($namespaceClass);

            return $reflection->isInstantiable();
        }

        return false;
    }

    /**
     * @param string $type
     *
     * @throws \ReflectionException
     *
     * @return null|TypeFieldAbstract|TypeOfValidAbstract
     */
    final public function getTypeByName(string $type)
    {
        if ($this->hasType($type)) {
            $namespaceClass = $this->getNamespaceComponent() . "{$type}";

            return new $namespaceClass();
        }
    }

    /**
     * @throws \ReflectionException
     *
     * @return array
     */
    final public function getTypesWithTitle(): array
    {
        $typesWithTitle = [];
        /** @var TypeFieldAbstract|TypeFieldAbstract|TypeOfValidAbstract $type */
        foreach ($this->getTypesObject() as $type) {
            $typesWithTitle[$type->getName()] = $type->getTitle();
        }

        return $typesWithTitle;
    }

    /**
     * @throws \ReflectionException
     *
     * @return array
     */
    final public function getObjectTypes(): array
    {
        $objectTypes = [];
        /** @var TypeFieldAbstract|TypeFieldAbstract|TypeOfValidAbstract $type */
        foreach ($this->getTypesObject() as $type) {
            $objectTypes[$type->getName()] = $type;
        }

        return $objectTypes;
    }

    /**
     * @throws \ReflectionException
     *
     * @return \Generator
     */
    private function getTypesObject()
    {
        $files = scandir($this->getPathByComponents());
        foreach ($files as $file) {
            $type = str_replace('.php', '', $file);
            $namespaceClass = $this->getNamespaceComponent() . "{$type}";
            if (class_exists($namespaceClass)) {
                $reflection = new \ReflectionClass($namespaceClass);
                if ($reflection->isInstantiable()) {
                    yield new $namespaceClass();
                }
            }
        }
    }
}
