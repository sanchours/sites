<?php

namespace unit\data;

/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 25.08.2015
 * Time: 18:07.
 */
class TestHelper
{
    /**
     * Вызвать private метод класса в PHP.
     *
     * @param object $object
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public static function callPrivateMethod($object, $method, $args = [])
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);

        return $result;
    }

    /**
     * Получить значение закрытого свойства.
     *
     * @param object $object
     * @param string $sPropertyName
     *
     * @return mixed
     */
    public static function getClosedProperty($object, $sPropertyName)
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $propertyReflection = $classReflection->getProperty($sPropertyName);
        $iModifiers = $propertyReflection->getModifiers();
        $propertyReflection->setAccessible(\ReflectionProperty::IS_PUBLIC);
        $mValue = $propertyReflection->getValue($object);
        $propertyReflection->setAccessible($iModifiers);

        return $mValue;
    }

    /**
     * Установить значение закрытого свойства.
     *
     * @param object $object
     * @param string $sPropertyName
     * @param mixed $mValue
     */
    public static function setClosedProperty($object, $sPropertyName, $mValue)
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $propertyReflection = $classReflection->getProperty($sPropertyName);
        $iModifiers = $propertyReflection->getModifiers();
        $propertyReflection->setAccessible(\ReflectionProperty::IS_PUBLIC);
        $propertyReflection->setValue($object, $mValue);
        $propertyReflection->setAccessible($iModifiers);
    }
}
