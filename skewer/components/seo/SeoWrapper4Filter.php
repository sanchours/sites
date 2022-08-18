<?php

namespace skewer\components\seo;

use skewer\base\site\Site;
use skewer\build\Catalog\Filters\model\FilterSettings4Card;
use skewer\components\filters\IndexedFilter;
use skewer\components\regions\RegionHelper;
use yii\base\UserException;

/**
 * Class SeoWrapper4Filter - класс-обертка.
 * Добавляет фильтру след.функциональность:
 * Парсинг seo-данных:
 *  - meta-title, meta-description, meta-keywords
 *  - h1
 *  - хлебные крошки.
 */
class SeoWrapper4Filter
{
    /** @var IndexedFilter - объект фильтра */
    protected $oFilter;

    /** @var FilterSettings4Card Настройки фильтра для карточки */
    protected $oFilterSettings;

    /** @var int - ид текущего раздела */
    protected $iCurrentSectionId;

    /**
     * @__construct
     *
     * @param IndexedFilter $oFilter
     * @param $iCurrentSectionId
     */
    public function __construct(IndexedFilter $oFilter, $iCurrentSectionId)
    {
        $this->oFilter = $oFilter;
        $this->iCurrentSectionId = $iCurrentSectionId;

        if ($oARFilterSettings = FilterSettings4Card::findOneByCard($oFilter->getExtCard())) {
            $this->oFilterSettings = $oARFilterSettings;
        }
    }

    /**
     * Получить массив меток (тех.имя поля=>значение поля) для парсинга seo-полей фильтра.
     *
     * @param $bIncludeLabelsWithMultipleValues - включать метки, содержащие несколько значений параметра?
     *
     * @throws UserException
     *
     * @return array
     */
    protected function getLabels4FilterField($bIncludeLabelsWithMultipleValues)
    {
        $aOut = [];

        $aFilterData = $this->getData4BuildingSeoData($bIncludeLabelsWithMultipleValues);

        $aLabels = array_fill_keys(array_keys($this->oFilter->getFilterFields()), '');

        $aDataTitle = $this->oFilter->convertArrayIdToTitle($aFilterData);

        foreach ($aDataTitle as &$mTitleItem) {
            if (is_array($mTitleItem)) {
                $mTitleItem = implode(',', $mTitleItem);
            }
        }

        $aLabels = array_replace($aLabels, $aDataTitle);

        // Заменяем тех.имена поле их названиями
        foreach ($aLabels as $sFieldName => $aLabel) {
            $oCurrentField = $this->oFilter->getFilterFields()[$sFieldName];
            $aOut[$oCurrentField->getFieldTitle()] = $aLabel;
        }

        return $aOut;
    }

    /**
     * Парсинг заголовка H1 данными полей карточки, используя шаблон указанный в "Настройки фильтров" или дефолтный $sDefTpl.
     *
     * @return string
     */
    public function generateH1()
    {
        return self::parseByTpl('alt_title', '{Fields}');
    }

    /**
     * Парсинг мета-тега title данными полей карточки, используя шаблон указанный в "Настройки фильтров" или дефолтный $sDefTpl.
     *
     * @return string
     */
    public function generateMetaTitle()
    {
        return self::parseByTpl('meta_title', 'Купить{Fields}', true);
    }

    /**
     * Парсинг мета-тега description данными полей карточки, используя шаблон указанный в "Настройки фильтров" или дефолтный $sDefTpl.
     *
     * @return string
     */
    public function generateMetaDescription()
    {
        return self::parseByTpl('meta_description', '{Fields}', true);
    }

    /**
     * Парсинг мета-тега keywords данными полей карточки, используя шаблон указанный в "Настройки фильтров" или дефолтный $sDefTpl.
     *
     * @return string
     */
    public function generateMetaKeywords()
    {
        return self::parseByTpl('meta_keywords', '{Fields}', true);
    }

    /**
     * Распарсит строку данными полей карточки, используя шаблон $sElement или дефолтный $sDefTpl.
     *
     * @param string $sElement - поле модели FilterSettings4Card, хранящее шаблон с метками([Метка])
     * @param string $sDefTpl - дефолтный шаблон, используемый если для карточки фильтра не были созданы "Настройки фильтра"
     * {Fields} - внутренняя метка, содер-ая поля фильтра
     * @param bool $bParseLabelsWithMultipleValues - парсить метки с несколькими значениями параметра?
     *
     * @throws UserException - в случае несуществуещего свойства $sElement у AR FilterSettings4Card
     *
     * @return string
     */
    private function parseByTpl($sElement, $sDefTpl = '{Fields}', $bParseLabelsWithMultipleValues = false)
    {
        $aLabels = $this->getLabels4FilterField($bParseLabelsWithMultipleValues);

        if ($this->oFilterSettings) {
            if (!$this->oFilterSettings->hasAttribute($sElement)) {
                throw new UserException(sprintf('Неизвестный атрибут %s', $sElement));
            }

            $sTpl = $this->oFilterSettings->{$sElement};
            $aLabels += Api::getCommonSeoLabels($this->iCurrentSectionId);
        } else {
            $sFieldsLabel = '';
            foreach (array_keys($aLabels) as $sNameLabel) {
                $sFieldsLabel .= ' [' . $sNameLabel . ']';
            }

            $sTpl = str_replace('{Fields}', $sFieldsLabel, $sDefTpl);
        }

        $sOut = self::replaceLabels($sTpl, $aLabels);

        return $sOut;
    }

    /**
     * Парсинг staticContent1 данными полей карточки, используя шаблон указанный в "Настройки фильтров".
     *
     * @return bool|string
     */
    public function generateStaticContent()
    {
        $aLabels = $this->getLabels4FilterField(false) + Api::getCommonSeoLabels($this->iCurrentSectionId);

        if ($this->oFilterSettings) {
            $sTpl = $this->oFilterSettings->staticContent1;
            $sOut = self::replaceLabels($sTpl, $aLabels);

            return $sOut;
        }

        return false;
    }

    /**
     * Заменяет метки вида [Метка] в строке $sTpl данными из $aLables.
     *
     * @param string $sTpl - строка дял парсинга
     * @param array $aLabels - метки замены
     *
     * @return string
     * */
    private static function replaceLabels($sTpl, $aLabels)
    {
        $sOut = TemplateRow::replaceLabels($sTpl, $aLabels);
        $sOut = trim($sOut);
        $sOut = self::mb_ucfirst($sOut);

        return $sOut;
    }

    /**
     * Возвращает массив данных для построения "хлебных крошек".
     *
     * @return array
     * Пример:
     *     [
     *         ['title' => 'Casio', 'link' => 'chasy/filter/brand=casio/'],
     *         ['title' => 'Casio Золото', 'link' => 'chasy/filter/brand=casio;case_material=zoloto/'],
     *         ['title' => 'Casio Золото Кожа', 'link' => '']
     *     ]
     */
    public function getBreadcrumbsItems()
    {
        $aOut = [];

        $aData4BreadCrumbs = $this->getData4BuildingSeoData(false);

        for ($i = 1; $i <= count($aData4BreadCrumbs); ++$i) {
            $aData4BreadCrumbsItem = array_slice($aData4BreadCrumbs, 0, $i);
            $sTitle = $this->buildTitleByFilterData($aData4BreadCrumbsItem);
            $aData4Url = $this->oFilter->converArrayIdToAlias($aData4BreadCrumbsItem);
            $link = $this->oFilter->buildUrlByFilterData($aData4Url);

            $bIsLastItem = ($i == count($aData4BreadCrumbs));

            $aOut[] = [
                'title' => $sTitle,
                'link' => $link,
                'selected' => $bIsLastItem,
            ];
        }

        return $aOut;
    }

    /**
     * Мультибайтовый ucfirst.
     *
     * @param  string $str - строка
     * @param  string $encoding - кодировка строки
     *
     * @return string
     */
    public static function mb_ucfirst($str, $encoding = 'utf-8')
    {
        $sFirstChar = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);

        return $sFirstChar . mb_substr($str, 1, null, $encoding);
    }

    /**
     * По массиву, содержащему условия фильтра построит заголовок.
     *
     * @param array $aFilterData
     *
     * @return string
     */
    protected function buildTitleByFilterData($aFilterData)
    {
        $aData = $this->oFilter->convertArrayIdToTitle($aFilterData);

        $aIntersect = array_intersect_key($this->oFilter->getFilterFields(), $aData);
        $aData = array_replace($aIntersect, $aData);

        foreach ($aData as &$item) {
            if (is_array($item)) {
                $item = implode(',', $item);
            }
        }

        $sTitle = implode(' ', $aData);

        return $sTitle;
    }

    /**
     * Получить данные фильтра для построения seo-данных, а именно:
     * -  для meta-тегов
     * -  для заголовка h1
     * -  для хлебных крошкек
     * -  для текстов в разделе.
     *
     * @param $bIncludeFieldWithMultipleValues - включать поля c несколькими значениями?
     *
     * @return array
     */
    protected function getData4BuildingSeoData($bIncludeFieldWithMultipleValues)
    {
        $aOut = [];

        foreach ($this->oFilter->getData() as $sFieldName => $mVal) {
            $aFilterFields = $this->oFilter->getFilterFields();

            $oFilterField = $aFilterFields[$sFieldName];

            // Пропускаем поля, значения которых не могут иметь заголовков
            if (!$oFilterField->canHaveTitle()) {
                continue;
            }

            // Пропускаем поля, имеющие два и более значений
            if (!$bIncludeFieldWithMultipleValues && count($mVal) >= 2) {
                continue;
            }

            $aOut[$sFieldName] = $mVal;
        }

        return $aOut;
    }

    /**
     * Построит канонический урл(rel=canonical) для страницы.
     *
     * @throws UserException
     * @throws \skewer\components\config\Exception
     *
     * @return string
     */
    public function buildCanonicalUrl()
    {
        // Данные фильтра
        $data4Url = $this->getData4BuildingSeoData(true);

        // Получаем alias-ы значений
        $data4Url = $this->oFilter->converArrayIdToAlias($data4Url);

        if (RegionHelper::isInstallModuleRegion()) {
            return RegionHelper::getCanonical(
                $this->oFilter->buildUrlByFilterData($data4Url)
            );
        }

        return Site::httpDomain() . $this->oFilter->buildUrlByFilterData($data4Url);
    }
}
