<?php

namespace skewer\components\search;

use skewer\base\orm\Query;
use skewer\base\section\Tree;

class Selector
{
    /**
     * Имя модуля для каталожного поиска.
     */
    const catalogClassName = 'CatalogViewer';

    /**
     * Максимальна длина текста, оображаемого в результатах поиска.
     *
     * @var int
     */
    private static $iLength = 500;

    /**
     * Длина текста, которую возьмем для резки, если не нашли в мелком кусочке.
     *
     * @var int
     */
    private static $iSlice = 33;

    /**
     * Текст для поиска.
     *
     * @var string
     */
    private $sSearchText = '';

    /**
     * Страница выборки.
     *
     * @var int
     */
    private $iPage = 0;

    /**
     * Размер выборки.
     *
     * @var int
     */
    private $iLimit = 0;

    /**
     * Тип поиска.
     *
     * @var int
     */
    private $iSearchType = 0;

    /**
     * Тип области для поиска.
     *
     * @var int
     */
    private $iType = 0;

    /**
     * Раздел для поиска.
     *
     * @var int
     */
    private $iSection = 0;

    /**
     * Поиск и в подразделах.
     *
     * @var bool
     */
    private $bInSubsection = false;

    /**
     * Исключенные разделы.
     *
     * @var array
     */
    private $aDenySection = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Создание экземпляра класса для выборки.
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Задаем текст для поиска.
     *
     * @param $sSearchText
     *
     * @return $this
     */
    public function searchText($sSearchText)
    {
        $this->sSearchText = $sSearchText;

        return $this;
    }

    /**
     * Задание пределов выборки.
     *
     * @param $iLimit
     * @param $iPage
     *
     * @return $this
     */
    public function limit($iLimit, $iPage)
    {
        $this->iPage = $iPage > 0 ? $iPage : 1;
        $this->iLimit = $iLimit;

        return $this;
    }

    /**
     * Тип поиска.
     *
     * @param $iSearchType
     *
     * @return $this
     */
    public function searchType($iSearchType)
    {
        $this->iSearchType = $iSearchType;

        return $this;
    }

    /**
     * Тип области для поиска.
     *
     * @param $iType
     *
     * @return $this
     */
    public function type($iType)
    {
        $this->iType = $iType;

        return $this;
    }

    /**
     * Раздел для поиска.
     *
     * @param $iSection
     *
     * @return $this
     */
    public function section($iSection)
    {
        $this->iSection = $iSection;

        return $this;
    }

    /**
     * Исключенные разделы.
     *
     * @param $aSection
     *
     * @return $this
     */
    public function denySection($aSection)
    {
        $this->aDenySection = $aSection;

        return $this;
    }

    /**
     * Поиск и в подразделах.
     *
     * @param $bSubsections
     *
     * @return $this
     */
    public function subsections($bSubsections)
    {
        $this->bInSubsection = (bool) $bSubsections;

        return $this;
    }

    /**
     * Отбросить окончания слов в зависимости от типа поиска.
     *
     * @param $sSearchText
     * @param $iSearchType
     *
     * @return string
     */
    private static function rebuildSearchString($sSearchText, $iSearchType)
    {
        $iSearchType = Type::getValid($iSearchType);

        if (Type::useStemmer($iSearchType)) {
            $sSearchText = Stemmer::rebuildString($sSearchText);
        }

        return $sSearchText;
    }

    /**
     * Выполнение поиска.
     *
     * @throws \Exception
     *
     * @return array
     */
    public function find()
    {
        $sFullRequest = strip_tags($this->sSearchText);
        $sSearchText = static::rebuildSearchString($this->sSearchText, $this->iSearchType);

        $aData = [
            'start' => ($this->iPage - 1) * $this->iLimit,
            'record_count' => (int) $this->iLimit,
        ];

        switch ($this->iSearchType) {
            case Type::anyWord:
            case Type::allWords:

            $sSearchText = Api::prepareSearchText($sSearchText);

                $aTextParts = explode(' ', $sSearchText);
                $aTextParts = array_diff($aTextParts, ['']);

                foreach ($aTextParts as &$sPart) {
                    $sPart = trim($sPart);

                    if ($this->iSearchType == Type::anyWord) {
                        $sPart = $sPart . '*';
                    }
                    if ($this->iSearchType == Type::allWords) {
                        $sPart = '+' . $sPart . '*';
                    }
                }

                $aData['search_text'] = implode(' ', $aTextParts);
                break;

            case Type::exact:

                $sSearchText = Api::prepareSearchText($sSearchText, true);
                $aData['search_text'] = '"' . $sSearchText . '"*';
                break;

            default:
                throw new \Exception("Unknown search type [{$this->iSearchType}]");
        }

        $sClassNameCondition = '';
        $sOrder = '';
        switch ($this->iType) {
            case Type::inCatalog:
                $sClassNameCondition = "`class_name` = '" . self::catalogClassName . "' AND ";
                $sOrder = 'MATCH (`search_text`) AGAINST (:search_text IN BOOLEAN MODE) DESC,';
                break;
            case Type::inInfo:
                $sClassNameCondition = "`class_name` != '" . self::catalogClassName . "' AND ";
                break;
        }

        $searchSection = 0;
        if ($this->iSection) {
            $searchSection = $this->iSection;

            if ($this->bInSubsection) {
                $searchSection = $this->getSubSections($this->iSection);
                $searchSection[] = $this->iSection;
            }

            if (!is_array($searchSection)) {
                $searchSection = [(int) $searchSection];
            } else {
                $searchSection = array_map(create_function('$a', 'return (int)$a;'), $searchSection);
            }
        }

        $aData['language'] = \Yii::$app->language;

        $sQuery = '
            SELECT
            SQL_CALC_FOUND_ROWS
                `search_title`,
                `search_text`,
                `text`,
                `href`,
                `class_name`,
                `section_id`,
                `object_id`,
                MATCH (`search_text`) AGAINST (:search_text IN BOOLEAN MODE) AS `rel`
            FROM `search_index`
            WHERE `status`=1 AND `use_in_search`=1 AND 
            `language` = :language AND
            ' .
            $sClassNameCondition .
            ($searchSection ? ' `section_id` IN (' . implode(',', $searchSection) . ') AND ' : '') .
            (count($this->aDenySection) ? '`section_id` NOT IN (' . implode(',', $this->aDenySection) . ') AND ' : '') .
            'MATCH (`search_text`) AGAINST (:search_text IN BOOLEAN MODE) > 0
        ORDER BY
        ' . $sOrder . '
            `rel` DESC
            LIMIT :start, :record_count;';

        $rResult = Query::SQL($sQuery, $aData);

        $aItems = [];
        while ($aRow = $rResult->fetchArray()) {
            $aItems['items'][] = $aRow;
        }

        if (isset($aItems['items'])) {
            $aItems['items'] = self::mark($aItems['items'], $sSearchText, $this->iSearchType);
        }

        $oQuery = Query::SQL('SELECT FOUND_ROWS() as rows;');

        $aItems['count'] = ($iCount = $oQuery->getValue('rows')) ? $iCount : 0;

        return $aItems;
    }

    /**
     * Возвращает список всех подразделов (включая сам раздел).
     *
     * @param $parent
     *
     * @return array
     */
    private function getSubSections($parent)
    {
        if (!is_array($parent)) {
            $parent = [$parent];
        }

        $aResult = [];

        do {
            $aItems = Tree::getSubSections($parent, true, true);
            $aResult = array_merge($aResult, $aItems);
            $parent = $aItems;
        } while ($aItems);

        return $aResult;
    }

    /**
     * В задачи функции вложено:
     * 1. поиск вхождений
     * 2. маркировка текста
     * 3. ранжирование на основе маркировки текста
     * 4. обрезка.
     *
     * @param mixed $aItems
     * @param mixed $sSearchText
     * @param mixed $sSearchType
     */
    public static function mark($aItems, $sSearchText, $sSearchType)
    {
        if (!empty($aItems['not_found']) or !empty($aItems['form'])) {
            return true;
        }
        $sSearchText = trim(strip_tags($sSearchText));
        $search_type = $sSearchType;

        $aWords = explode(' ', $sSearchText);
        $aWords = array_map(static function ($word) {return trim($word); }, $aWords);
        $sWordWithoutEndings = Selector::rebuildSearchString($sSearchText, $search_type);
        $aWordsWithoutEndings = explode(' ', $sWordWithoutEndings);

        foreach ($aItems as $keyItem => $item) {
            $markTitle = false; //Флаги указывающий, что закрасили заголовок целиком
            $markText = false;  //Флаги указывающий, что закрасили текст целиком
            $iIndexRanking = 0; //Индекс ранжирования для устранения ошибок в поиске MySQL
            //Временное хранилище, которое потом запушим и заодно обрежем текст
            $sItemTitle = $item['search_title'];
            $sItemText = $item['text'];

            //search full query in title
            $resPosTitle = mb_stripos($sItemTitle, $sSearchText, null, 'UTF-8');
            if ($resPosTitle !== false) {
                $sItemTitle = self::putMark($sSearchText, $sItemTitle, $resPosTitle);
                $markTitle = true;
                $iIndexRanking += 2;
            }
            //in body
            $resPosText = mb_stripos($sItemText, $sSearchText, null, 'UTF-8');
            if ($resPosText !== false) {
                $sItemText = self::putMark($sSearchText, $sItemText, $resPosText);
                $markText = true;
                $iIndexRanking += 1.5;
            }

            //search word
            if (!$markTitle or !$markText) {
                foreach ($aWords as $keyWord => $word) {
                    //skip words less than or equal 3 symbols
                    if (mb_strlen($word) <= 3) {
                        continue;
                    }

                    if (!$markTitle) {
                        $resPosTitle = mb_stripos($sItemTitle, $word, null, 'UTF-8');
                        if ($resPosTitle !== false) {
                            $sItemTitle = self::putMark($word, $sItemTitle, $resPosTitle);
                            $markTitle = true;
                            $iIndexRanking += 0.9;
                        }
                    }

                    if (!$markText) {
                        $resPosText = mb_stripos($sItemText, $word, null, 'UTF-8');
                        if ($resPosText !== false) {
                            $sItemText = self::putMark($word, $sItemText, $resPosText);
                            $markText = true;
                            $iIndexRanking += 0.5;
                        }
                    }
                }
            }

            //search as chunk word without endings
            if (!$markTitle or !$markText) {
                foreach ($aWordsWithoutEndings as $keyWord => $word) {
                    //skip words less than or equal 3 symbols
                    if (mb_strlen($word) <= 3) {
                        continue;
                    }

                    if (!$markTitle) {
                        $resPosTitle = mb_stripos($sItemTitle, $word, null, 'UTF-8');
                        if ($resPosTitle !== false) {
                            $sItemTitle = self::putMark($word, $sItemTitle, $resPosTitle, true);
                            $markTitle = true;
                            $iIndexRanking += 0.5;
                        }
                    }

                    if (!$markText) {
                        $resPosText = mb_stripos($sItemText, $word, null, 'UTF-8');
                        if ($resPosText !== false) {
                            $sItemText = self::putMark($word, $sItemText, $resPosText, true);
                            $markText = true;
                            $iIndexRanking += 0.4;
                        }
                    }
                }
            }

            $sItemText = self::getFragment($sItemText, $resPosText);
            $aItems[$keyItem]['search_title'] = $sItemTitle;
            $aItems[$keyItem]['text'] = $sItemText;
            $aItems[$keyItem]['rel'] = (float) $aItems[$keyItem]['rel'] + $iIndexRanking;
        }

        return $aItems;
    }

    /**
     * Вставка подсветки
     * Пример: putMark('Мы ели', 'Мы ели кашу', 0)
     * На выходе -> '<mark>Мы ели</mark> кашу'
     * Пример(2): putMark('оценк', 'Оценка бизнеса', 0, true)
     * -> '<mark>Оценка</mark> бизнеса'.
     *
     * @param $needle
     * @param $haystack string
     * @param $position
     * @param $bWordFull bool заполнить окончание слова
     *
     * @return string
     */
    private static function putMark($needle, $haystack, $position, $bWordFull = false)
    {
        $sSearchTextLength = mb_strlen($needle, 'UTF-8');
        $markBegin = '<mark>';
        $markEnd = '</mark>';

        if ($bWordFull) {
            $aResSearch = null;
            $iRes = preg_match("/({$needle})[а-яА-Яa-zA-Z0-9_]{1,}/ui", $haystack, $aResSearch, PREG_OFFSET_CAPTURE, null);
            if ($iRes !== 0) {
                $sSearchTextLength = mb_strlen($aResSearch[0][0], 'UTF-8');
            }
        }

        $out = mb_substr($haystack, 0, $position, 'UTF-8')
            . $markBegin
            . mb_substr($haystack, $position, $sSearchTextLength, 'UTF-8')
            . $markEnd
            . mb_substr($haystack, $position + $sSearchTextLength, null, 'UTF-8');

        return $out;
    }

    /**
     * Получить фрагмент текста c динамичной обрезкой и точками спереди и в конце, если это необходимо.
     *
     * @param $sString
     * @param $iPosition
     * @param $iLength
     *
     * @return string
     */
    private static function getFragment($sString, $iPosition, $iLength = 0)
    {
        $iLength = $iLength ?: self::$iLength;
        $iPosition -= self::$iSlice; //offset to begin for normally shown
        if ($iPosition < 0) {
            $iPosition = 0;
        }
        if ($iPosition) {
            $sString = '...  ' . mb_substr($sString, $iPosition, null, 'UTF-8');
        } //add dots before text

        if (mb_strlen($sString, 'UTF-8') > $iLength) { //cut excess
            $sString = mb_substr($sString, 0, $iLength, 'UTF-8');
            $sString = mb_substr($sString, 0, mb_strrpos($sString, ' '), 'UTF-8') . ' ...'; //add dots after text
        }

        return $sString;
    }
}
