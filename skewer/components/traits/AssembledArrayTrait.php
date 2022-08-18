<?php

namespace skewer\components\traits;

trait AssembledArrayTrait
{
    private $assembledArray = [];

    /**
     * Сбор многомернога массива в одномерный по ключам с разделителем - .
     * @param $array
     * @param null $keyPath
     */
    private function setAssembleDimensionalArray($array, $keyPath = null)
    {
        foreach ($array as $key => $value) {
            $key = $keyPath !== null ? "{$keyPath}.{$key}" : $key;
            if (is_array($value)) {
                $this->setAssembleDimensionalArray($value, $key);
            } else {
                $this->assembledArray[$key] = $value;
            }
        }
    }

    /**
     * Получение одномерного массива из многомерного
     * @param array $multidimensionalArray
     * @return array
     */
    public function getAssembleArray($multidimensionalArray)
    {
        $this->setAssembleDimensionalArray($multidimensionalArray);

        return $this->assembledArray;
    }
}
