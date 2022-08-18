<?php

namespace skewer\helpers;

use yii\base\UserException;

/**
 * Класс для транслитерации строк.
 */
class Transliterate
{
    /**
     * Массив соответствия символов.
     *
     * @var array
     */
    protected static $aConvert = [
        "'" => '',
        '`' => '',
        'а' => 'a', 'А' => 'A',
        'б' => 'b', 'Б' => 'B',
        'в' => 'v', 'В' => 'V',
        'г' => 'g', 'Г' => 'G',
        'д' => 'd', 'Д' => 'D',
        'е' => 'e', 'Е' => 'E',
        'ё' => 'e', 'Ё' => 'E',
        'ж' => 'zh', 'Ж' => 'Zh',
        'з' => 'z', 'З' => 'Z',
        'и' => 'i', 'И' => 'I',
        'й' => 'j', 'Й' => 'J',
        'к' => 'k', 'К' => 'K',
        'л' => 'l', 'Л' => 'L',
        'м' => 'm', 'М' => 'M',
        'н' => 'n', 'Н' => 'N',
        'о' => 'o', 'О' => 'O',
        'п' => 'p', 'П' => 'P',
        'р' => 'r', 'Р' => 'R',
        'с' => 's', 'С' => 'S',
        'т' => 't', 'Т' => 'T',
        'у' => 'u', 'У' => 'U',
        'ф' => 'f', 'Ф' => 'F',
        'х' => 'h', 'Х' => 'H',
        'ц' => 'c', 'Ц' => 'C',
        'ч' => 'ch', 'Ч' => 'Ch',
        'ш' => 'sh', 'Ш' => 'Sh',
        'щ' => 'sch', 'Щ' => 'Sch',
        'ъ' => '', 'Ъ' => '',
        'ы' => 'y', 'Ы' => 'Y',
        'ь' => '', 'Ь' => '',
        'э' => 'e', 'Э' => 'E',
        'ю' => 'yu', 'Ю' => 'Yu',
        'я' => 'ya', 'Я' => 'Ya',
    ];

    /**
     * Шаблон для поиска запрещенных символов.
     *
     * @var string
     */
    protected static $sDeprecatedPattern = '/(\s)+|[^-_a-z0-9]+/Uis';

    /**
     * Разделитель.
     *
     * @var string
     */
    protected static $sDelimiter = '-';

    /**
     * Заменяет все неугодные символы.
     *
     * @static
     *
     * @param string $sIn - входная строка
     * @param bool $bToLower
     *
     * @return string
     */
    public static function change($sIn, $bToLower = true)
    {
        $sOut = iconv('UTF-8', 'UTF-8//IGNORE', strtr($sIn, static::$aConvert));

        return $bToLower ? mb_strtolower($sOut) : $sOut;
    }

    /**
     * Заменяет запрещенные символы на разделители.
     *
     * @static
     *
     * @param array|string $mIn
     *
     * @return array|string
     */
    public static function changeDeprecated($mIn)
    {
        return preg_replace(static::$sDeprecatedPattern, static::$sDelimiter, $mIn);
    }

    /**
     * Сливает несколько подряд идущих разделителей в один.
     *
     * @static
     *
     * @param array|string $mIn
     *
     * @return array|string
     */
    public static function mergeDelimiters($mIn)
    {
        return preg_replace('/[' . static::$sDelimiter . ']{2,}/s', static::$sDelimiter, $mIn);
    }

    /**
     * Возвращает текущее значение разделителя.
     *
     * @static
     *
     * @return string
     */
    public static function getDelimiter()
    {
        return static::$sDelimiter;
    }

    /**
     * Устаналвивает $sDelimiter в качестве основного разделителя.
     *
     * @static
     *
     * @param string $sDelimiter
     *
     * @return mixed
     */
    public static function setDelimiter($sDelimiter)
    {
        return static::$sDelimiter = $sDelimiter;
    }

    /**
     * Переводим строку с пробелами и поджеркаваниями в верблюжий стиль.
     *
     * @param $sName
     *
     * @return string
     */
    public static function toCamelCase($sName)
    {
        return trim(str_replace(' ', '', ucwords(str_replace('_', ' ', $sName))));
    }

    /**
     * Изменяет формат даты.
     *
     * @param $sDate
     *
     * @return string
     */
    public static function date2db($sDate)
    {
        $aData = explode('.', $sDate);

        if (count($aData) != 3) {
            return '';
        }

        return $aData[2] . '-' . $aData[1] . '-' . $aData[0];
    }

    public static function generateAlias($alias)
    {
        $alias = self::change($alias);
        $alias = self::changeDeprecated($alias);
        $alias = self::mergeDelimiters($alias);
        $alias = trim($alias, '-');

        if (is_numeric($alias)) {
            $alias = 'g' . $alias;
        }

        return $alias;
    }

    public static function genSysName($alias, $pref = 'g')
    {
        $alias = self::change($alias);
        $alias = preg_replace('/(\s)+|[^_a-z0-9]+/Uis', static::$sDelimiter, $alias);
        $alias = self::mergeDelimiters($alias);
        $alias = trim($alias, '-');
        $alias = str_replace('-', '_', $alias);

        if (!$alias) {
            throw new UserException(\Yii::t('card', 'error_no_sysname'));
        }
        if (is_numeric($alias) || is_numeric(mb_substr($alias, 0, 1))) {
            $alias = $pref . $alias;
        }

        return $alias;
    }
}
