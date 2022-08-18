<?php

namespace skewer\components\filters;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\base\orm\state\StateSelect;
use skewer\base\SysVar;
use skewer\components\catalog;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

/**
 * Прототип компонента "Фильтр"
 * Class FilterPrototype.
 */
abstract class FilterPrototype
{
    /** @const Виджет - Фоторама */
    const TYPE_FOTORAMA = 'fotorama';

    /** @const Виджет - Плитка */
    const TYPE_TILE = 'tile';
    /** @const Виджет - Фотогалерея */
    const TYPE_GALLERY = 'gallery';

    /** @const int Тип фильтра - стандартный */
    const FILTER_TYPE_STANDARD = 'filter_standard';

    /** @const int Тип фильтра - индексируемый поисковыми системами*/
    const FILTER_TYPE_INDEX = 'filter_indexed';

    /** @var array|int Раздел(-ы), в пределах которого выполняется фильтрация товаров */
    protected $mSearchSection = 0;

    /** @var int Раздел, в котором выводится фильтр */
    protected $iShowSection = 0;

    /** @var null|string Тех.имя поля коллекция. Задаётся в случае использования фильтра на странице элемента коллекции */
    protected $sCollectionField;

    /** @var null|int|string Значение элемента коллекции. Задаётся в случае использования фильтра на странице элемента коллекции */
    protected $sCollectionValue;

    protected $bUseExtCard = false;

    protected $sBaseCard = '';

    protected $sExtCard = '';

    /** @var array Массив имён полей, исключенных из показа в форме фильтра */
    protected $aExcludedFieldsFromShow = [];

    /** @var widgets\Prototype[] виджеты фильтра */
    protected $aFilterFields = [];

    /** @var array Установленные/применненные условия фильтра в формате массива */
    protected $data = [];

    protected $shadowParams = false;

    /**
     * Получить объект фильтра по разделу(-ам).
     *
     * @param array|int $mSection - раздел(-ы) с фильтруемыми данными
     * @param $iShowSection - раздел показа фильтра
     * @param $sFilterConditions - условия фильтра
     * @param $iTypeFilter - тип фильтра
     * @param array $aExcludedFieldsFromShow - поля, исключенные из показа в форме фильтра
     *
     * @throws ServerErrorHttpException
     *
     * @return FilterPrototype
     */
    public static function getInstanceBySection($mSection = 0, $iShowSection, $sFilterConditions, $iTypeFilter, $aExcludedFieldsFromShow = [])
    {
        $oSelf = self::getInstanceByType($iTypeFilter);

        $oSelf->aExcludedFieldsFromShow = $aExcludedFieldsFromShow;

        $oSelf->mSearchSection = $mSection;

        $oSelf->iShowSection = $iShowSection;

        $oSelf->shadowParams = (bool) SysVar::get('catalog.shadow_param_filter');

        $oSelf->initFields();

        $oSelf->initData($sFilterConditions);

        return $oSelf;
    }

    /**
     * Получить объект фильтра по карточке.
     *
     * @param int $card - карточка с фильтруемыми данными
     * @param $iShowSection - раздел показа фильтра
     * @param $sFilterConditions - условия фильтра
     * @param $iTypeFilter - тип фильтра
     * @param array $aExcludedFieldsFromShow - поля, исключенные из показа в форме фильтра
     *
     * @throws ServerErrorHttpException
     *
     * @return FilterPrototype
     */
    public static function getInstanceByCard($card, $iShowSection, $sFilterConditions, $iTypeFilter, $aExcludedFieldsFromShow = [])
    {
        $oSelf = self::getInstanceByType($iTypeFilter);

        $oSelf->aExcludedFieldsFromShow = $aExcludedFieldsFromShow;

        $oSelf->iShowSection = $iShowSection;

        $oSelf->shadowParams = (bool) SysVar::get('catalog.shadow_param_filter');

        $oSelf->initFields($card);

        $oSelf->initData($sFilterConditions);

        return $oSelf;
    }

    /**
     * Получить объект фильтра для страницы элемента коллекции.
     *
     * @param string $sCollectionField - тех.имя поля коллекция
     * @param int $iElementCollectionId - значение элемента коллекции
     * @param $iShowSection - раздел показа фильтра
     * @param $sFilterConditions - условия фильтра
     * @param $iTypeFilter - тип фильтра
     * @param array $aExcludedFieldsFromShow - поля, исключенные из показа в форме фильтра
     *
     * @throws ServerErrorHttpException
     *
     * @return FilterPrototype
     */
    public static function getInstance4CollectionPage($sCollectionField, $iElementCollectionId, $iShowSection, $sFilterConditions, $iTypeFilter, $aExcludedFieldsFromShow = [])
    {
        $iCardId = (int) catalog\Card::get4Field($sCollectionField);

        $oInstance = self::getInstanceByCard($iCardId, $iShowSection, $sFilterConditions, $iTypeFilter, $aExcludedFieldsFromShow);

        $oInstance->sCollectionField = $sCollectionField;

        $oInstance->sCollectionValue = $iElementCollectionId;

        return $oInstance;
    }

    /**
     * Фабричный метод. Создаст объект фильтра по его типу.
     *
     * @param string $sTypeFilter
     *
     * @throws ServerErrorHttpException
     *
     * @return FilterPrototype
     */
    public static function getInstanceByType($sTypeFilter)
    {
        switch ($sTypeFilter) {
            case self::FILTER_TYPE_STANDARD:
                $oInstance = new StandardFilter();
                break;
            case self::FILTER_TYPE_INDEX:
                $oInstance = new IndexedFilter();
                break;
            default:
                throw new ServerErrorHttpException(sprintf('Unknown filter type - [%s]', $sTypeFilter));
        }

        return $oInstance;
    }

    /**
     * Инициализация полей для разела.
     *
     * @param string $card
     *
     * @throws \Exception
     */
    public function initFields($card = '')
    {
        if ($this->mSearchSection) {
            $aCardList = catalog\Section::getCardList($this->mSearchSection);
        } else {
            $aCardList = [$card];
        }

        if (!$aCardList) {
            return;
        }

        $oBaseCard = ft\Cache::get(catalog\Card::DEF_BASE_CARD);
        $iBaseCardId = $oBaseCard->getEntityId();

        if ((count($aCardList) == 1) and (reset($aCardList) != $iBaseCardId)) {
            $this->bUseExtCard = true;

            $iCard = array_shift($aCardList);

            $oModel = ft\Cache::get($iCard);

            $this->sExtCard = $oModel->getName();

            $oParModel = ft\Cache::get($oModel->getParentId());

            $this->sBaseCard = $oParModel->getName();

            $this->aFilterFields = Api::getFilterFieldsByCard($iCard, $this);
        } else {
            $oModel = ft\Cache::get(catalog\Card::DEF_BASE_CARD);
            $this->sBaseCard = $oModel->getName();
            $this->aFilterFields = Api::getFilterFieldsByCard(catalog\Card::DEF_BASE_CARD, $this);
        }
    }

    /**
     * Инициализация данных(уже установленных условий) фильтра.
     *
     * @param string $sFilterConditions - условия фильтра
     */
    abstract protected function initData($sFilterConditions);

    /**
     * Сбор данных для парсинга формы.
     *
     * @return array
     */
    public function parse()
    {
        // проверка наличия активных товаров в разделе/карточке
        if ($this->sBaseCard) {
            $query = $this->getQuery();

            $query
                ->fields('count(*) AS cnt', true)
                ->where('active', 1)
                ->asArray();

            $row = $query->getOne();

            if (!$row || ($row['cnt'] == 0)) {
                return [];
            }
        }

        $aOut = [];

        foreach ($this->aFilterFields as $oFilterField) {
            if (in_array($oFilterField->getFieldName(), $this->aExcludedFieldsFromShow)) {
                continue;
            }

            $aFieldData = ArrayHelper::getValue($this->data, $oFilterField->getFieldName(), []);
            $aFieldDataHtml = $oFilterField->parse($aFieldData);
            if ($aFieldDataHtml) {
                $aOut[$oFilterField->getFieldName()] = $aFieldDataHtml;
            }
        }

        return $aOut;
    }

    /**
     * Формирует объект запросника.
     *
     * @return \skewer\base\orm\state\StateSelect
     */
    public function getQuery()
    {
        $query = Query::SelectFrom('co_' . $this->sBaseCard);

        if ($this->bUseExtCard) {
            $query->join('inner', 'ce_' . $this->sExtCard, 'ce_' . $this->sExtCard, 'co_' . $this->sBaseCard . '.id=ce_' . $this->sExtCard . '.id');
        }

        if ($this->mSearchSection) {
            $query
                ->join('inner', 'cl_section', '', 'co_' . $this->sBaseCard . '.id=goods_id')
                ->on('section_id', $this->mSearchSection);
        }

        if (isset($this->sCollectionField,$this->sCollectionValue)) {
            // находим карточку поля коллекция
            $iCardId = (int) catalog\Card::get4Field($this->sCollectionField);

            // запрашиваем модель этого поля у карточки
            $oField = ft\Cache::get($iCardId)->getFiled($this->sCollectionField);

            $oRel = $oField->getModel()->getOneFieldRelation($oField->getName());

            // Если это мультиколлекция
            if ($oRel and $oRel->getType() == ft\Relation::MANY_TO_MANY) {
                $sLinkTableName = $oField->getLinkTableName();

                $query
                    ->join('inner', $sLinkTableName, $sLinkTableName, 'co_' . catalog\Card::DEF_BASE_CARD . '.id=`' . $sLinkTableName . '`.' . ft\Relation::INNER_FIELD)
                    ->on(sprintf('%s.%s', $sLinkTableName, ft\Relation::EXTERNAL_FIELD), $this->sCollectionValue);
            } else {
                $query->where($this->sCollectionField, $this->sCollectionValue);
            }
        }

        return $query;
    }

    /**
     * Перевод данных фильтра к формату, где каждый элемент является массивом
     *
     * @param array $aData - входные данные
     *
     * @return array
     * */
    public function canonizeToArrayFormat($aData)
    {
        $aOut = [];

        foreach ($aData as $sFieldName => $mDataItem) {
            if (!isset($this->aFilterFields[$sFieldName])) {
                continue;
            }

            $oFilterField = $this->aFilterFields[$sFieldName];

            $aOut[$sFieldName] = $oFilterField->canonizeValue($mDataItem);
        }

        return $aOut;
    }

    /**
     * Фильтрация значений полей фильтра.
     *
     * @param array $aData - входные данные
     *
     * @return array
     * */
    public function filteringInputValues($aData)
    {
        $aOut = [];

        foreach ($aData as $sFieldName => $aDataItem) {
            if (!isset($this->aFilterFields[$sFieldName])) {
                continue;
            }

            $oFilterField = $this->aFilterFields[$sFieldName];

            $mTmp = $oFilterField->filterInputVal($aDataItem);

            if ($mTmp === false) {
                continue;
            }

            $aOut[$sFieldName] = $aDataItem;
        }

        return $aOut;
    }

    /**
     * Получить данные фильтра.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Получить расширенную карточку фильтра.
     *
     * @return string
     */
    public function getExtCard()
    {
        return $this->sExtCard;
    }

    /**
     * Фильтр использует раскраску недоступных значений ?
     *
     * @return bool
     */
    public function isShadowParams()
    {
        return $this->shadowParams;
    }

    /**
     * Получить массив полей фильтра(виджеты).
     *
     * @return widgets\Prototype[]
     */
    public function getFilterFields()
    {
        return $this->aFilterFields;
    }

    /**
     * Добавляет условия фильтра в запрос $oQuery, пропуская условия соответствующи полям $aExcludedFields.
     *
     * @param StateSelect $oQuery
     * @param array $aExcludedFields
     *
     * @return bool - true - если было добавлено хотя бы одно условие, false - в противном случае
     */
    public function addFilterConditionsToQuery(StateSelect $oQuery, $aExcludedFields = [])
    {
        $bFilterUsed = false;

        foreach ($this->aFilterFields as $sFilterName => $filterField) {
            if (in_array($sFilterName, $aExcludedFields)) {
                continue;
            }

            $bConditionApplied = $filterField->addFilterConditionToQuery($oQuery, $this->data);

            // Условие фильтра применено ?
            if ($bConditionApplied) {
                $bFilterUsed = true;
            }
        }

        return $bFilterUsed;
    }

    /**
     * Получить уникальные значения для поля фильтра.
     *
     * @param catalog\field\Prototype $oField - каталожное поле
     * @param bool $bLimitedFilterConditions  - ограничить выборку условиями фильтра?
     *
     * @return array
     */
    public function getUniqueValues4FilterField(catalog\field\Prototype $oField, $bLimitedFilterConditions = false)
    {
        $aOut = [];

        $query = $this->getQuery();

        $oRel = $oField->getFtField()->getModel()->getOneFieldRelation($oField->getName());

        if ($oRel and $oRel->getType() == ft\Relation::MANY_TO_MANY) {
            $sLinkTableName = $oField->getFtField()->getLinkTableName();

            $query
                ->join('inner', $sLinkTableName, $sLinkTableName, 'co_' . catalog\Card::DEF_BASE_CARD . '.id =' . $sLinkTableName . '.' . ft\Relation::INNER_FIELD)
                ->fields(
                    sprintf('DISTINCT %s.`%s`  AS fld', $sLinkTableName, ft\Relation::EXTERNAL_FIELD),
                    true
                );
        } else {
            $sFieldName = $oField->getName();
            $query
                ->fields("DISTINCT {$sFieldName} AS fld", true);
        }

        // Добавить в запрос условия фильтра, исключая условие соответствующее полю $oField
        if ($bLimitedFilterConditions) {
            $this->addFilterConditionsToQuery($query, [$oField->getName()]);
        }

        $query
            ->where('active', 1)
            ->asArray();

        while ($row = $query->each()) {
            $aOut[] = $row['fld'];
        }

        return $aOut;
    }

    /**
     * Получить disallow правила фильтра для Robots.txt.
     *
     * @return array
     */
    public function getRobotsDisallowPatterns()
    {
        return [];
    }
}
