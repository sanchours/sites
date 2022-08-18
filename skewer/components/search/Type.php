<?php

namespace skewer\components\search;

/**
 * Класс набор типов поиска.
 */
class Type
{
    /** любое слово */
    const anyWord = 0;

    /** все слова */
    const allWords = 1;

    /** точное совпадение */
    const exact = 2;

    /** По всему */
    const inAll = 0;

    /** Информационный */
    const inInfo = 1;

    /** По каталогу */
    const inCatalog = 2;

    /**
     * Отдает флаг использования словоформ для поискового типа.
     *
     * @param int $iSearchType тип поиска
     *
     * @return bool
     */
    public static function useStemmer($iSearchType)
    {
        return in_array($iSearchType, [
            self::anyWord,
            self::allWords,
        ]);
    }

    /**
     * Отдает валидный тип поиска.
     *
     * @param int $iSearchType
     *
     * @return int
     */
    public static function getValid($iSearchType)
    {
        if (!in_array($iSearchType, [
            self::anyWord,
            self::allWords,
            self::exact,
        ])) {
            $iSearchType = self::anyWord;
        }

        return $iSearchType;
    }

    /**
     * Список типов по поисковой области.
     *
     * @return array
     */
    public static function getTypeList()
    {
        return [
            static::inAll => \Yii::t('search', 'type_all'),
            static::inInfo => \Yii::t('search', 'type_info'),
            static::inCatalog => \Yii::t('search', 'type_catalog'),
        ];
    }

    /**
     * Список типов.
     *
     * @return array
     */
    public static function getSearchTypeList()
    {
        return [
            static::allWords => \Yii::t('search', 'all_words'),
            static::anyWord => \Yii::t('search', 'any_words'),
            static::exact => \Yii::t('search', 'phrase_criteria'),
        ];
    }
}
