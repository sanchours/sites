<?php

declare(strict_types=1);

namespace skewer\components\forms\traits;

/**
 * Trait ParserExtJsTrait
 * отвечает за формирование и разбор одномерного массива, сформированного из свойст объекта.
 */
trait ParserExtJsTrait
{
    private $_delimiter = '_';

    /**
     * Объединение в один массив с ключами вида
     * ["nameObject.property" => "value"].
     *
     * @param array $aggregator
     * @param string $nameKey
     *
     * @return array
     */
    public function combineInOneArray(array $aggregator, string $nameKey = ''): array
    {
        $result = [];
        foreach ($aggregator as $key => $value) {
            $glueKeys = $nameKey ? "{$nameKey}" . $this->_delimiter . "{$key}" : $key;
            if (is_array($value)) {
                $result += self::combineInOneArray($value, $glueKeys);
                continue;
            }

            $result[$glueKeys] = $value;
        }

        return $result ?: [];
    }

    /**
     * Разбор массива вида ["nameObject.property" => "value"]
     * в многомерный массив вида ["nameObject" => ["property" => "value"]].
     *
     * @param array $array
     *
     * @return array
     */
    public function parseGluedArray(array $array): array
    {
        $result = [];
        foreach ($array as $nameSettings => $value) {
            $objectAndProperty = explode($this->_delimiter, $nameSettings);

            if (isset($objectAndProperty[1])) {
                $result[$objectAndProperty[0]][$objectAndProperty[1]] = $value;
            } else {
                $result[$objectAndProperty[0]] = $value;
            }
        }

        return $result;
    }
}
