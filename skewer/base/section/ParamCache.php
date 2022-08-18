<?php

namespace skewer\base\section;

use skewer\helpers\Files;
use yii\base\ErrorException;

/**
 * Класс для работы с кэшем параметров
 * От же является оперативным хранилицем
 *
 * Сброс происходит при каждой модификации параметров и дерева разделов.
 * В худшем случае мы получим время, сопостовимое с работой без кэша.
 */
class ParamCache
{
    /** @var int Процент от разрещенной памяти */
    const CACHE_MEMORY_LIMIT_PERCENT = 60;

    /** @var int Минимальное количество записей в кеше */
    const MINIMUM_CACHE_ITEM = 50;

    /** @var int Разрешенное количество байт для работы скрипта */
    private static $memoryLimit;

    /**
     * Массив запрошенных данных
     * Содержит трехуровневый массив
     * [
     *      278 => [ // id раздела
     *          0 => [ // набор параметров
     *              'id' => 2948
     *              'parent' => 5
     *              'group' => g1
     *              'name' => n1
     *              'value' => v51
     *              'title' => title
     *              'access_level' => 0
     *              'show_val' => test
     *          ],
     *          1 => [],
     *          ...
     *      ],
     *      789 => [...]
     * ].
     *
     * @var array[]
     */
    private static $cache = [];

    /**
     * Флаг использования кэша параметров.
     *
     * @var bool
     */
    public static $useCache = true;

    /**
     * Отдает массив данных параметров по указанномк разделу
     * Все, с учеотом рекурсивного наследования
     * В случае отсутствия данных выкинет исключение, поэтому следует использовать с методом has.
     *
     * @param int $id
     *
     * @throws ErrorException
     *
     * @return array|mixed
     */
    public static function get($id)
    {
        $id = (int) $id;

        if (!self::has($id)) {
            throw new ErrorException('No cache data for [' . $id . '] section');
        }

        return self::$cache[$id];
    }

    /**
     * Проверяет наличие данных по указанному id раздела.
     *
     * @param int $id
     *
     * @return bool
     */
    public static function has($id)
    {
        return isset(self::$cache[$id]);
    }

    /**
     * Устанавливает данные в кэш.
     *
     * @param $id
     * @param $data
     */
    public static function set($id, $data)
    {
        self::normalizeCache();
        self::$cache[$id] = $data;
    }

    /**
     * Сбрасывает кэш.
     */
    public static function clear()
    {
        self::$cache = [];
    }

    /**
     * Нормализует кэш массив, если потребляемая память приближается к лимиту.
     */
    public static function normalizeCache()
    {
        if (!self::$memoryLimit) {
            $sMemoryLimit = ini_get('memory_limit');
            $memoryLimit = Files::strToByte($sMemoryLimit);
            self::$memoryLimit = $memoryLimit * self::CACHE_MEMORY_LIMIT_PERCENT / 100;
        }

        while (self::$memoryLimit < memory_get_usage() && count(self::$cache) > self::MINIMUM_CACHE_ITEM) {
            array_shift(self::$cache);
        }
    }
}
