<?php

namespace skewer\components\search;

/**
 * Класс для поддержки словоформ в поиске.
 */
class Stemmer
{
    /**
     * Пересобирает строку поискового запроса для работы использования в поисковом запросе.
     *
     * @param string $sSearchText исходная строка для поиска
     *
     * @return string
     */
    public static function rebuildString($sSearchText)
    {
        require_once RELEASEPATH . 'libs/stemmer/Lingua_Stem_Ru.php';

        $oStemmer = new \Lingua_Stem_Ru();

        $aSearch = explode(' ', $sSearchText);
        $aSearch = array_diff($aSearch, ['']);

        foreach ($aSearch as &$psSearch) {
            $sSearchStem = $oStemmer->stem_word($psSearch);

            if (mb_strlen($sSearchStem) >= Api::MIN_WORD_LEN) {
                $psSearch = $sSearchStem;
            }
        }

        return implode(' ', $aSearch);
    }
}
