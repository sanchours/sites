<?php

namespace skewer\components\search;

use skewer\components\auth\Auth;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsSelector;
use skewer\components\search\models\SearchIndex;
use yii\helpers\ArrayHelper;

/**
 * API для работы с поисковым индексом
 * method static Api find.
 *
 * @method SearchIndex getOne
 */
class Api
{
    /** Минимальный размер символов в одном слове для поиска. Настравивается через конфигурационный параметр mysql ft_min_word_len */
    const MIN_WORD_LEN = 4;

    /** событие по сбору активных поисковых движков */
    const EVENT_GET_ENGINE = 'event_get_engine';

    /** событие по поиску в системе администрирования */
    const EVENT_CMS_SEARCH = 'event_cms_search';

    /** @var null|Prototype[] список поисковых движков */
    private static $aList = null;

    /**
     * Отдает запись поискового индекса по классу и id.
     *
     * @param string $sClassName имя класса
     * @param int $iObjectId id объекта
     *
     * @return null|SearchIndex
     */
    public static function get($sClassName, $iObjectId)
    {
        $oRow = SearchIndex::find()->where(['class_name' => $sClassName, 'object_id' => $iObjectId])->one();

        return $oRow ? $oRow : null;
    }

    /**
     * Отдает одну запись по ссылке, если найдет
     *
     * @param $sHref
     *
     * @return null|SearchIndex
     */
    public static function getByHref($sHref)
    {
        $oRow = SearchIndex::findOne(['href' => $sHref]);

        return $oRow ? $oRow : null;
    }

    /**
     * Удаление всех записей для раздела.
     *
     * @static
     *
     * @param int $iSectionId Идентификатор раздела
     *
     * @return int количество удаленных записей
     */
    public static function removeFromIndexBySection($iSectionId)
    {
        return SearchIndex::deleteAll(['section_id' => $iSectionId]);
    }

    /**
     * отдает набор пар 'имя идентификатора' => 'класс с namespace', участвующих в индексе в порядке приоритетов.
     *
     * @return \string[] array
     */
    public static function getResourceList()
    {
        if (self::$aList === null) {
            $oEvent = new GetEngineEvent();
            \Yii::$app->trigger(self::EVENT_GET_ENGINE, $oEvent);
            self::$aList = $oEvent->getList();
        }

        return self::$aList;
    }

    /**
     * Поиск Search модуля по названию класса.
     *
     * @param string $sName псевдоним движка
     *
     * @return null|Prototype объект поискового движка или null
     */
    public static function getSearch($sName)
    {
        $list = self::getResourceList();

        if (isset($list[$sName])) {
            /** @var Prototype $s */
            $s = new $list[$sName]();
            $s->provideName($sName);

            return $s;
        }
    }

    /**
     * Подготовить строку текста к индексации или для поиска через MATCH() AGAINST().
     *
     * @param string $psText Указатель на обрабатываемый текст
     * @param bool $bRepStopWords Заменить неиндексируемые слова? (флаг используется только при добавлении в индекс)
     *
     * @return string
     */
    public static function prepareSearchText($psText, $bRepStopWords = false)
    {
        /** Список слов, которыми нельзя называть колонку с полнотекстовым индексом и которые mysql не индексирует этим индексом */
        static $sStopWords = 'able|about|above|according|accordingly|across|actually|after|afterwards|again|against|all|allow|allows|almost|alone|along|already|also|although|always|am|among|amongst|an|and|another|any|anybody|anyhow|anyone|anything|anyway|anyways|anywhere|apart|appear|appreciate|appropriate|are|around|as|aside|ask|asking|associated|at|available|away|awfully|be|became|because|become|becomes|becoming|been|before|beforehand|behind|being|believe|below|beside|besides|best|better|between|beyond|both|brief|but|by|came|can|cannot|cant|cause|causes|certain|certainly|changes|clearly|co|com|come|comes|concerning|consequently|consider|considering|contain|containing|contains|corresponding|could|course|currently|definitely|described|despite|did|different|do|does|doing|done|down|downwards|during|each|edu|eg|eight|either|else|elsewhere|enough|entirely|especially|et|etc|even|ever|every|everybody|everyone|everything|everywhere|ex|exactly|example|except|far|few|fifth|first|five|followed|following|follows|for|former|formerly|forth|four|from|further|furthermore|get|gets|getting|given|gives|go|goes|going|gone|got|gotten|greetings|had|happens|hardly|has|have|having|he|hello|help|hence|her|here|hereafter|hereby|herein|hereupon|hers|herself|hi|him|himself|his|hither|hopefully|how|howbeit|however|ie|if|ignored|immediate|in|inasmuch|inc|indeed|indicate|indicated|indicates|inner|insofar|instead|into|inward|is|it|its|itself|just|keep|keeps|kept|know|known|knows|last|lately|later|latter|latterly|least|less|lest|let|like|liked|likely|little|look|looking|looks|ltd|mainly|many|may|maybe|me|mean|meanwhile|merely|might|more|moreover|most|mostly|much|must|my|myself|name|namely|nd|near|nearly|necessary|need|needs|neither|never|nevertheless|new|next|nine|no|nobody|non|none|noone|nor|normally|not|nothing|novel|now|nowhere|obviously|of|off|often|oh|ok|okay|old|on|once|one|ones|only|onto|or|other|others|otherwise|ought|our|ours|ourselves|out|outside|over|overall|own|particular|particularly|per|perhaps|placed|please|plus|possible|presumably|probably|provides|que|quite|qv|rather|rd|re|really|reasonably|regarding|regardless|regards|relatively|respectively|right|said|same|saw|say|saying|says|second|secondly|see|seeing|seem|seemed|seeming|seems|seen|self|selves|sensible|sent|serious|seriously|seven|several|shall|she|should|since|six|so|some|somebody|somehow|someone|something|sometime|sometimes|somewhat|somewhere|soon|sorry|specified|specify|specifying|still|sub|such|sup|sure|take|taken|tell|tends|th|than|thank|thanks|thanx|that|thats|the|their|theirs|them|themselves|then|thence|there|thereafter|thereby|therefore|therein|theres|thereupon|these|they|think|third|this|thorough|thoroughly|those|though|three|through|throughout|thru|thus|to|together|too|took|toward|towards|tried|tries|truly|try|trying|twice|two|un|under|unfortunately|unless|unlikely|until|unto|up|upon|us|use|used|useful|uses|using|usually|value|various|very|via|viz|vs|want|wants|was|way|we|welcome|well|went|were|what|whatever|when|whence|whenever|where|whereafter|whereas|whereby|wherein|whereupon|wherever|whether|which|while|whither|who|whoever|whole|whom|whose|why|will|willing|wish|with|within|without|wonder|would|yes|yet|you|your|yours|yourself|yourselves|zero';

        static $add, $len;
        $len or $len = Api::MIN_WORD_LEN - 1;
        $add or $add = str_repeat('_', $len);

        // Разбить короткие слова с дефисами на отдельные, чтобы можно было находить по части слова до дефиса
        $psText = preg_replace("/(^|[\\s])([^\\s]{1,{$len}}?)(-)/", '$1$2 $2$3', $psText);

        // Замена служебного символа дефиса на нижнее подчёркивание
        $psText = str_replace(['.', '-'], '_', $psText);

        // Замена всех не текстовых символов, включая специализированных mysql операторов конструкции AGAINST, на разделение слов
        $psText = preg_replace('/[\W]{1,}/u', ' ', $psText);

        // Увеличение размера коротких слов методом добавления доп. символов
        $psText = preg_replace("/(^|\\s)([\\w]{1,{$len}})(?=\\s|$)/ui", "$1$2{$add}$3", $psText);

        // Заменить неиндексируемые слова. Внимание! Эта замена должна быть обязательно в самом конце, чтобы не добавляла лишних символов "_" для коротких слов (!)
        $bRepStopWords and $psText = preg_replace("/\\b({$sStopWords})\\b/i", '$1_', $psText);

        return $psText;
    }

    /**
     * Получение данных для каталожного поиска.
     *
     * @param string $sSearchText - поисковая строка
     * @param int $iOnPage
     * @param int $iPage
     * @param string $type
     * @param int $searchType
     * @param int $searchSection
     * @param bool $bSubsection
     * @param bool $sTypeSearch тип поиска - для мобильного или для пк
     *
     * @return array $aItems
     */
    public static function getCatalogData($sSearchText, $iOnPage, $iPage, $type, $searchType, $searchSection, $bSubsection, $sTypeSearch = 0)
    {
        /* Если есть запрещенные политикой разделы - исключаем из выборки */
        $aDenySections = ($res = Auth::getDenySections('public')) ? $res : [];

        $aItems = Selector::create()
            ->searchText($sSearchText)
            ->limit($iOnPage, $iPage)
            ->type(Type::inCatalog)
            ->searchType($type)
            ->type($searchType)
            ->section($searchSection)
            ->denySection($aDenySections)
            ->subsections($bSubsection)
            ->find();

        if (is_array($aItems) && isset($aItems['count']) && $aItems['count'] and isset($aItems['items'])) {
            /** Делаем выборка из каталога */
            $aObjects = ArrayHelper::map($aItems['items'], 'object_id', 'object_id');
            $aResult = self::getGoods($aObjects, $sTypeSearch);
            if (!$aResult) {
                return [];
            }

            if (!$sTypeSearch) {
                $aResult['count'] = $aItems['count'];
            }

            return $aResult;
        }

        return [];
    }

    /**
     * Получение данных для общего и информационного поиска.
     *
     * @param string $sSearchText - поисковая строка
     * @param int $iOnPage
     * @param int $iPage
     * @param string $type
     * @param int $searchType
     * @param int $searchSection
     * @param bool $bSubsection
     * @param int $iLength - Максимальна длина текста
     *
     * @return array $aItems
     */
    public static function getInfoData($sSearchText, $iOnPage, $iPage, $type, $searchType, $searchSection, $bSubsection, $iLength)
    {
        $aDenySections = ($res = Auth::getDenySections('public')) ? $res : [];

        /** Делаем выборку */
        $aItems = Selector::create()
            ->searchText($sSearchText)
            ->limit($iOnPage, $iPage)
            ->searchType($type)
            ->type($searchType)
            ->section($searchSection)
            ->denySection($aDenySections)
            ->subsections($bSubsection)
            ->find();

        if (is_array($aItems) && !empty($aItems['count']) && !empty($aItems['items'])) {
            self::addShowLinkFlag($aItems['items']);

            foreach ($aItems['items'] as $iKey => &$aItem) {
                $aItem['number'] = (int) $iKey + 1 + ($iPage - 1) * $iOnPage;
                self::parseItem($aItem, $iLength);
            }

            return $aItems;
        }

        return [];
    }

    /**
     * Записям массива добавляет флаг разрешения выводить ссылку на запись.
     *
     * @param  array $aItems
     */
    private static function addShowLinkFlag(&$aItems)
    {
        $aItemsGroupByClassName = ArrayHelper::map($aItems, 'object_id', 'section_id', 'class_name');

        // Массив раздел(id) => флаг скрытия детальной(true-скрыта/false-открыта)
        $aSectionToHiddenDetailFlag = [];

        if (isset($aItemsGroupByClassName['CatalogViewer'])) {
            // Разделы собранных товаров
            $aCatalogSections = array_unique($aItemsGroupByClassName['CatalogViewer']);

            foreach ($aCatalogSections as $iSectionId) {
                $bFlag = false;

                if (\skewer\components\catalog\Card::isDetailHidden($iSectionId)) {
                    $bFlag = true;
                }

                $aSectionToHiddenDetailFlag[$iSectionId] = $bFlag;
            }
        }

        // добавим флаг разрешения показа ссылки
        foreach ($aItems as &$item) {
            if ($item['class_name'] == 'CatalogViewer') {
                $item['showLink'] = !$aSectionToHiddenDetailFlag[$item['section_id']];
            } else {
                $item['showLink'] = true;
            }
        }
    }

    private static function parseItem(&$aItem = [], $iLength)
    {
        $aItem['module_title'] = static::getModuleTitle($aItem['class_name']);

        if (isset($aItem['text']) && mb_strlen($aItem['text']) > $iLength) {
            $aItem['text'] = mb_substr($aItem['text'], 0, $iLength);
            $aItem['text'] = mb_substr($aItem['text'], 0, mb_strrpos($aItem['text'], ' ')) . ' ...';
        }
    }

    /**
     * Отдает имя класса по имени модуля.
     *
     * @param string $sClassName имя модуля
     *
     * @return string
     */
    private static function getModuleTitle($sClassName)
    {
        $oEngine = Api::getSearch($sClassName);

        return $oEngine ? $oEngine->getModuleTitle() : '-';
    }

    /**
     * Получениу товаров.
     *
     * @param array $aObjects
     * @param bool $sTypeSearch {0 - для сайта, 1 - для мобильника}
     *
     * @return array|bool
     */
    private static function getGoods($aObjects = [], $sTypeSearch = 0)
    {
        $aGoods = GoodsSelector::getList(Card::DEF_BASE_CARD, false)
            ->condition('id IN ?', $aObjects)
            ->condition('active', 1)
            ->parse();

        if (!$aGoods) {
            return false;
        }
        $aGoods = ArrayHelper::index($aGoods, 'id');

        $aResult = [];
        /* Сортировка в соответствии с результатами поиска */
        foreach ($aObjects as $iObjectId) {
            if (isset($aGoods[$iObjectId])) {
                switch ($sTypeSearch) {
                    case 0:
                        $aResult['items'][] = $aGoods[$iObjectId];
                        break;
                    case 1:
                        $aResult[] = $aGoods[$iObjectId];
                        break;
                }
                unset($aGoods[$iObjectId]);
            }
        }

        return $aResult;
    }

    /**
     * Индексирует первые 3 символа слов строки
     * Из слов строки $sText длиной 2+ символов образовывает комбинации
     * 1. 'два первых символа слова' . '___'
     * 2. 'три первых символа слова' . '___'.
     *
     * @param $sText string
     *
     * @return string
     */
    public static function indexFirstCharsString($sText)
    {
        $aOut = [];

        $aPatterns = [
            '/\b[\w]{2}/ui',
            '/\b[\w]{3}/ui',
        ];

        foreach ($aPatterns as $sPattern) {
            $aMatch = [];
            preg_match_all($sPattern, $sText, $aMatch);
            if (isset($aMatch[0])) {
                foreach ($aMatch[0] as $match) {
                    $aOut[] = $match . str_repeat('_', Api::MIN_WORD_LEN - 1);
                }
            }
        }

        return implode(' ', $aOut);
    }
}
