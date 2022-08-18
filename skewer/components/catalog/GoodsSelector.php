<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\base\section\Tree;
use skewer\base\Twig;
use skewer\components\catalog\model\GoodsTable;
use skewer\components\catalog\model\SectionTable;
use skewer\components\catalog\model\SemanticTable;
use skewer\components\filters\FilterPrototype;
use yii\helpers\ArrayHelper;

/**
 * Класс для выборки товарных позиций для вывода на клиентской части
 * Class GoodsSelector.
 */
class GoodsSelector extends SelectorPrototype
{
    /** @var bool Флаг фильтрации по полям */
    private $bApplyAttrFilter = false;

    /** @var bool Флаг факта использования фильтрации */
    private $bFilterUsed = false;

    /** @var bool Флаг необходимости использовать группировку для товаров */
    private $useGrouping = false;

    /**
     * Отдает алиас товара по его ID.
     *
     * @param $iGoodId
     *
     * @throws \Exception
     *
     * @return false|ft\model\Field
     */
    public static function getGoodsAlias($iGoodId)
    {
        $aAlias = Query::SelectFrom('co_base_card')
            ->fields('alias')
            ->where('id', $iGoodId)
            ->asArray()
            ->getOne();
        if (!$aAlias) {
            return false;
        }

        return $aAlias['alias'];
    }

    /**
     * Получить распарсенную каталожную позицию.
     *
     * @param int $id Id каталожной позиции
     * @param string $baseCard
     * @param bool $bAllFields Парсить все активные поля карточки(true) или только поля для детальной страницы(false)?
     * @param int $iCurrentSection - id текущего раздела. Если =0, вернет товар без seo - данных
     *
     * @return array|bool
     */
    public static function get($id, $baseCard = Card::DEF_BASE_CARD, $bAllFields = false, $iCurrentSection = 0)
    {
        if (is_numeric($id)) {
            $oGoodsRow = GoodsRow::get($id, $baseCard);
        } else {
            $oGoodsRow = GoodsRow::getByAlias($id, $baseCard);
        }

        if (!$oGoodsRow) {
            return false;
        }

        $aGoodParams = model\GoodsTable::get($oGoodsRow->getRowId());

        $oParser = Parser::get($oGoodsRow->getFields(), $bAllFields ? ['active'] : ['active', 'show_in_detail']);

        $aGoodData = $oParser->parseGood($aGoodParams, $oGoodsRow->getData(), false);

        //  Если передан $iCurrentSection, то добавляем seo данные
        if ($iCurrentSection) {
            $aFakeVarGoods = [$aGoodData];
            $oParser->addSeoDataInGoods($aFakeVarGoods, $iCurrentSection);
            $aGoodData = reset($aFakeVarGoods);
        }

        return $aGoodData;
    }

    /**
     * Получить товар следующий за заданным
     *
     * @param int $iObjectId Ид объекта
     * @param int $iSectionId Ид раздела
     * @param array $aFilter Фильтр для сортировки
     * @param bool $bShortData отдаст только основные данные без парсингов
     *
     * @return array|bool
     */
    public static function getPrev($iObjectId, $iSectionId, /** @noinspection PhpUnusedParameterInspection */
                                   $aFilter = [], $bShortData = false)
    {
        if (!($row = model\SectionTable::getPrev($iObjectId, $iSectionId))) {
            return false;
        }

        if ($bShortData) {
            return $row;
        }

        if (!($oGoodsRow = GoodsRow::get($row['goods_id'], $row['goods_card']))) {
            return false;
        }

        $aGoodParams = model\GoodsTable::get($oGoodsRow->getRowId());

        return Parser::get($oGoodsRow->getFields())
            ->parseGood($aGoodParams, $oGoodsRow->getData(), true);
    }

    /**
     * Получить товар идущий перед заданным
     *
     * @param int $iObjectId Ид объекта
     * @param int $iSectionId Ид раздела
     * @param array $aFilter Фильтр для сортировки
     * @param bool $bShortData отдаст только основные данные без парсингов
     *
     * @return array|bool
     */
    public static function getNext($iObjectId, $iSectionId, /** @noinspection PhpUnusedParameterInspection */
                                   $aFilter = [], $bShortData = false)
    {
        if (!($row = model\SectionTable::getNext($iObjectId, $iSectionId))) {
            return false;
        }

        if ($bShortData) {
            return $row;
        }

        if (!($oGoodsRow = GoodsRow::get($row['goods_id'], $row['goods_card']))) {
            return false;
        }

        $aGoodParams = model\GoodsTable::get($oGoodsRow->getRowId());

        return Parser::get($oGoodsRow->getFields())
            ->parseGood($aGoodParams, $oGoodsRow->getData(), true);
    }

    /**
     * Список товарных позиций для коллекции.
     *
     * @param $brand
     * @param $field
     * @param $value
     * @param bool $defaultOrder применить сортировку по умолчанию
     *
     * @throws Exception
     * @throws ft\Exception
     *
     * @return GoodsSelector
     */
    public static function getList4Collection($brand, $field, $value, $defaultOrder = true)
    {
        $oGoods = new self();

        $oGoods->selectCard(Card::get4FieldByEntityId($brand));
        $oGoods->initParser(['active', 'show_in_list']);

        // выборка по таблицам
        $sFieldLine = 'co_' . $oGoods->sBaseCard . '.*, base_id';

        $oGoods->oQuery = Query::SelectFrom('co_' . $oGoods->sBaseCard)
            ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . $oGoods->sBaseCard . '.id=base_id')
            ->on('base_id=parent');

        if ($oGoods->bUseExtCard) {
            $oGoods->oQuery->join('inner', 'ce_' . $oGoods->sExtCard, 'ext_card', 'ext_card.id=base_id');
            $sFieldLine .= ', ext_card.*';
        }

        if ($oGoods->aCardFields[$field]->getOption('editor')['name'] == ft\Editor::MULTICOLLECTION) {
            $oGoods->oQuery->join('inner', $oGoods->aCardFields[$field]->getLinkTableName(), 'mlt_collection', 'mlt_collection.__inner=base_id')
                ->on('mlt_collection.__external = ?', $value);

            if ($defaultOrder) {
                $oGoods->oQuery->order('mlt_collection' . '.__pos');
            }
        } else {
            if (isset($oGoods->aCardFields[$field])) {
                $oGoods->oQuery->where($field, $value);
            } else {
                $oGoods->oQuery->where('id', 0);
            }
        }

        $oGoods->oQuery->fields($sFieldLine)->asArray();

        $oGoods->bSorted = false;

        return $oGoods;
    }

    /**
     * Список товарных позиций для разделов.
     *
     * @param array|int $section Ид или список разедлов
     * @param array $aAttr - атрибуты выбираемых полей (active, show_in_list ..)
     * @param bool $bShowModification - выводить товары модификации ?
     *
     * @return GoodsSelector
     */
    public static function getList4Section($section, $aAttr = ['active', 'show_in_list'], $bShowModification = false)
    {
        $oGoods = new self();

        $oGoods->selectCard(self::card4Section($section));
        $oGoods->initParser($aAttr);

        // Выборка по таблицам. Поле section из таблицы c_goods используется для определения наличия раздела у товара
        // при получении списка для добавления сопутствующих товаров и товаров в комплекте
        $sFieldLine = 'co_' . $oGoods->sBaseCard . '.*, base_id, section';

        if (!$bShowModification) {
            $oGoods->oQuery = Query::SelectFrom('co_' . $oGoods->sBaseCard)
                ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . $oGoods->sBaseCard . '.id=base_id')
                ->on('base_id=parent')
                ->join('inner', SectionTable::getTableName(), '', 'co_' . $oGoods->sBaseCard . '.id=goods_id')
                ->on('section_id', $section);
        } else {
            $oGoods->oQuery = Query::SelectFrom('co_' . $oGoods->sBaseCard)
                ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . $oGoods->sBaseCard . '.id=base_id')
                ->join('inner', SectionTable::getTableName(), '', GoodsTable::getTableName() . '.parent=goods_id')
                ->on('section_id', $section);
        }

        if ($oGoods->bUseExtCard) {
            $oGoods->oQuery->join('inner', 'ce_' . $oGoods->sExtCard, 'ext_card', 'ext_card.id=base_id');
            $sFieldLine .= ', ext_card.*';
        }

        // отсекаем дубли одного товара в нескольких разделах
        if (is_array($section) && count($section) > 1) {
            $oGoods->useGrouping = true;
        }

        $oGoods->oQuery->fields($sFieldLine)->asArray();

        $oGoods->bSorted = true;

        return $oGoods;
    }

    /**
     * Выборка позиций для вывода общего списка по базовой карточки.
     *
     * @param int|string $card Базовая или расширенная карточка
     * @param bool $bOnlyFirstLevel флаг выбора только товаров первого уровне (без модификаций)
     *
     * @throws Exception
     *
     * @return GoodsSelector
     */
    public static function getList($card = Card::DEF_BASE_CARD, $bOnlyFirstLevel = true)
    {
        $oGoods = new self();

        $oGoods->selectCard($card);
        $oGoods->initParser(['active', 'show_in_list']);

        // Выборка по таблицам. Поле section из таблицы c_goods используется для определения наличия раздела у товара
        // при получении списка для добавления сопутствующих товаров и товаров в комплекте
        $sFieldLine = 'co_' . $oGoods->sBaseCard . '.*, base_id, section';

        $oGoods->oQuery = Query::SelectFrom('co_' . $oGoods->sBaseCard)
            ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . $oGoods->sBaseCard . '.id=base_id');

        if ($bOnlyFirstLevel) {
            $oGoods->oQuery->on('base_id=parent');
        }

        if ($oGoods->bUseExtCard) {
            $oGoods->oQuery->join('inner', 'ce_' . $oGoods->sExtCard, 'ext_card', 'ext_card.id=base_id');
            $sFieldLine .= ', ext_card.*';
        }

        $oGoods->oQuery->fields($sFieldLine)->asArray();

        return $oGoods;
    }

    public static function getListByIds($aIds, $card = Card::DEF_BASE_CARD, $bOnlyFirstLevel = true)
    {
        if (count($aIds) == 0) {
            return [];
        }

        $oGoods = self::getList($card, $bOnlyFirstLevel);

        $oGoods->oQuery->where('id', $aIds);

        return $oGoods;
    }

    /**
     * Список сопутствующих товаров.
     *
     * @param int $iObjectId Id товара
     *
     * @return GoodsSelector
     */
    public static function getRelatedList($iObjectId)
    {
        $oGoods = new self();

        $row = model\GoodsTable::get($iObjectId);

        $oGoods->selectCard($row['base_card_name']);
        $oGoods->initParser(['active', 'show_in_list']);

        $oGoods->oQuery = Query::SelectFrom('co_' . $oGoods->sBaseCard, $oGoods->sBaseCard)
            ->fields('co_' . $oGoods->sBaseCard . '.*, section,priority')
            ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . $oGoods->sBaseCard . '.id=base_id')
            ->on('base_id=parent')
            ->join('inner', SemanticTable::getTableName(), 'tbl_semantic', 'co_' . $oGoods->sBaseCard . '.id=child_id')
            ->on('semantic=?', Semantic::TYPE_RELATED)
            ->on('parent_id=?', $iObjectId)
            ->asArray();

        $oGoods->bSorted = true;

        return $oGoods;
    }

    public static function getRelatedList4ObjectRand($iObjectId, $iCurSectionId, $iNeedCount = 0, $aBanIds = [])
    {
        //Выборка разделов из которых выводим сопутствующие

        $oGoods = new self();

        $row = model\GoodsTable::get($iObjectId);

        $oGoods->selectCard($row['base_card_name']);

        $aSections = RelatedSections::getRelationsByPageId($iCurSectionId);

        if (empty($aSections)) {
            $aSections = [$iCurSectionId];
        }

        $aBanIds[] = $iObjectId;

        $iCountGoods = Query::SelectFrom('co_' . $oGoods->sBaseCard)
            ->fields('id')
            ->join('inner', SectionTable::getTableName(), '', 'co_' . $oGoods->sBaseCard . '.id=goods_id')
            ->on('section_id', $aSections)
            ->where('active', '1')
            ->whereRaw('id NOT IN(' . implode(',', $aBanIds) . ')')
            ->where('section_id', $aSections)
            ->getCount('id');

        $iRandCount = 100;

        if ($iCountGoods > $iRandCount) {
            $iOffset = random_int(0, $iCountGoods - $iRandCount);
        } else {
            $iOffset = 0;
        }

        $aGoods = Query::SelectFrom('co_base_card')
            ->fields('id')
            ->join('inner', SectionTable::getTableName(), '', 'co_base_card.id=goods_id')
            ->on('section_id', $aSections)
            ->where('active', '1')
            ->whereRaw('id NOT IN(' . implode(',', $aBanIds) . ')')
            ->where('section_id', $aSections)
            ->limit($iRandCount, $iOffset)
            ->asArray()
            ->getAll();

        $aGoods = ArrayHelper::getColumn($aGoods, 'id');

        if (count($aGoods) < $iNeedCount) {
            $iNeedCount = count($aGoods);
        }

        $aKeys = (count($aGoods) > 0) ? array_rand($aGoods, $iNeedCount) : [];
        $aGoodsIds = [];

        if (is_int($aKeys)) { // Если один ключ
            $aGoodsIds[] = $aGoods[$aKeys];
        } else {
            foreach ($aKeys as $key) {
                $aGoodsIds[] = $aGoods[$key];
            }
        }

        return self::getListByIds($aGoodsIds);
    }

    /**
     * Список товаров в комплекте.
     *
     * @param int $iObjectId Id товара
     *
     * @return GoodsSelector
     */
    public static function getIncludedList($iObjectId)
    {
        $oGoods = new self();

        $row = model\GoodsTable::get($iObjectId);

        $oGoods->selectCard($row['base_card_name']);
        $oGoods->initParser(['active', 'show_in_list']);

        $oGoods->oQuery = Query::SelectFrom('co_' . $oGoods->sBaseCard, $oGoods->sBaseCard)
            ->fields('co_' . $oGoods->sBaseCard . '.*, section')
            ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . $oGoods->sBaseCard . '.id=base_id')
            ->on('base_id=parent')
            ->join('inner', SemanticTable::getTableName(), 'tbl_semantic', 'co_' . $oGoods->sBaseCard . '.id=child_id')
            ->on('semantic=?', Semantic::TYPE_INCLUDE)
            ->on('parent_id=?', $iObjectId)
            ->asArray();

        $oGoods->bSorted = true;

        return $oGoods;
    }

    /**
     * Получение списка аналогов для товара.
     *
     * @param int $iObjectId Id объекта для которого запрашиваются модификации
     * @param int $iExcludeId Id Объекта для исключения из списка модификаций. По умолчанию = id родительского объекта
     *
     * @return GoodsSelector
     */
    public static function getModificationList($iObjectId, $iExcludeId = 0)
    {
        $aRow = model\GoodsTable::get($iObjectId);
        $iExcludeId = $iExcludeId ?: $aRow['parent'];

        $oGoods = new self();

        $oGoods->selectCard($aRow['ext_card_name']);
        $oGoods->initParser(['active', 'show_in_list']);

        $oGoods->oQuery = Query::SelectFrom('co_' . $oGoods->sBaseCard, $oGoods->sBaseCard)
            ->fields('co_' . $oGoods->sBaseCard . '.*,ext_card.*')
            ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . $oGoods->sBaseCard . '.id=base_id')
            ->on('parent=?', (int) $iObjectId)
            ->on('base_id<>?', $iExcludeId)
            ->join('inner', 'ce_' . $oGoods->sExtCard, 'ext_card', 'ext_card.id=base_id')
            ->order('modific_priority')
            ->asArray();

        $oGoods->bSorted = false;

        return $oGoods;
    }

    /**
     * Фильтрация списка каталожных позиций.
     *
     * @param FilterPrototype $oFilter
     *
     * @return bool
     */
    public function applyFilter(FilterPrototype $oFilter)
    {
        $this->bApplyAttrFilter = true;

        $this->bFilterUsed = $oFilter->addFilterConditionsToQuery($this->oQuery);

        $this->useGrouping = true;

        return $this->bFilterUsed;
    }

    /**
     * Парсинг набора товаров.
     *
     * @return array
     */
    public function parse()
    {
        // установка базовой сортировки если не задано поле
        if ($this->bSorted) {
            $this->oQuery->order('priority');
        }

        if ($this->useGrouping) {
            $this->oQuery->groupBy('co_' . $this->sBaseCard . '.id');
        }

        // Включить в выборку поля из таблицы описания товара, нужные для парсера
        $this->oQuery->fields(GoodsTable::getTableName() . '.*');

        $aGoodsList = $this->oQuery->asArray()->getAll();

        $aItems = [];
        foreach ($aGoodsList as &$aGood) {
            $aItems[] = $this->oParser->parseGood($aGood, $aGood, false);
        }

        if ($aItems) {
            //         Добавляем seo - данные
            if ($this->bWithSeo && ($iSectionId = $this->getInnerParam('iSectionId'))) {
                $this->oParser->addSeoDataInGoods($aItems, $iSectionId);
            }
        }

        return $aItems;
    }

    /**
     * Итератор выборки с парсером (используется в YandexExport).
     *
     * @return array|bool
     */
    public function parseEach()
    {
        // Инициализация итератора
        if (!$this->oQuery->getBEachIterator()) {
            // Включить в выборку поля из таблицы описания товара, нужные для парсера
            $this->oQuery
                ->fields(GoodsTable::getTableName() . '.*')
                ->asArray();

            // установка базовой сортировки если не задано поле
            if ($this->bSorted) {
                $this->oQuery->order('priority');
            }

            if ($this->useGrouping) {
                $this->oQuery->groupBy('co_' . $this->sBaseCard . '.id');
            }
        }

        if (!($aGood = $this->oQuery->each())) {
            return false;
        }

        return $this->oParser->parseGood($aGood, $aGood);
    }

    public function getArray(&$count)
    {
        // установка базовой сортировки если не задано поле
        if ($this->bSorted) {
            $this->oQuery->order('priority');
        }

        $goods = $this->oQuery->setCounterRef($count)->asArray()->getAll();

        foreach ($goods as &$good) {
            $good['price'] = Twig::priceFormat($good['price'], 0);
        }

        return $goods;
    }

    /**
     * Возвращает объект запросника.
     *
     * @return \skewer\base\orm\state\StateSelect
     */
    public function getQuery()
    {
        // установка базовой сортировки если не задано поле
        if ($this->bSorted) {
            $this->oQuery->order('priority');
        }

        return $this->oQuery;
    }

    /**
     * Возвращает карточку для построения списка товаров для разделов.
     *
     * @param int $section идентификатор раздела с товарами
     *
     * @return int
     */
    public static function card4Section($section)
    {
        // получаем список всех карточек товаров для раздела
        $cards = Section::getCardList($section);

        if (count($cards) == 1) {
            // если одна, то берем ее (вывод по одной расширенной карточке)
            $card = array_shift($cards);
        } elseif (count($cards) > 1) {
            // если больше одной, то берем базовую (вывод только по базовой)
            $card = Card::getBaseCard($cards[0]);
        } else {
            // карточки нет - либо ошибка, либо раздел пуст - выводим по дефолтной базовой
            $card = Card::DEF_BASE_CARD;
        }

        return $card;
    }

    /**
     * Задание условий на сортировку поля товара.
     *
     * @param string $sFieldName
     * @param string $sWay
     * @param bool $bCheckField Проверить поле на доступность сортировки
     *
     * @return $this
     */
    public function sort($sFieldName, $sWay = 'ASC', $bCheckField = true)
    {
        if (!$sFieldName) {
            return $this;
        }

        if ($bCheckField) {
            if (!count($this->aCardFields)) {
                $this->oQuery->order($sFieldName, $sWay);
                $this->bSorted = false;

                return $this;
            }

            /** @var ft\model\Field $field */
            if (!($field = ArrayHelper::getValue($this->aCardFields, $sFieldName, false))) {
                return $this;
            }

            if (!$field->getAttr('show_in_list') || !$field->getAttr('show_in_sortpanel')) {
                return $this;
            }

            // hack не обрабатываем пока связи ><
            if ($rel = $field->getFirstRelation()) {
                if ($rel->getType() == ft\Relation::MANY_TO_MANY) {
                    return $this;
                }
            }
        }

        $this->oQuery->order($sFieldName, $sWay);
        $this->bSorted = false;

        return $this;
    }

    /** Только товары из видимых разделов */
    public function onlyVisibleSections()
    {
        $this->oQuery
            ->where('section', Tree::getVisibleSections());

        return $this;
    }

    /**
     * Добавить в выборку поле, показывающее доступность базового раздела товара, которое будет содержать:
     * * 0, если раздел невидим, является ссылкой или отсутствует
     * * 1, иначе.
     */
    public function addAvailableSectionField()
    {
        $this->oQuery
            ->field('(`section` IN (' . implode(',', array_keys(Tree::getVisibleSections())) . ")) AS 'available_section'");

        return $this;
    }

    /**
     * сбрасывает активность всем НЕ обновленным товарам
     *
     * @param $extCard
     *
     * @throws \yii\db\Exception
     */
    public static function deactivateNonUpdated($extCard = null)
    {
        if ($extCard === null) {
            Query::SQL(
                'UPDATE `co_base_card` as card
              INNER JOIN `c_goods` as goods
              ON card.`id` = goods.`base_id`
              SET card.`active`=0
              WHERE card.`updated`=0',
                []
            );
        } else {
            Query::SQL(
                'UPDATE `co_base_card` as card
                  INNER JOIN `c_goods` as goods
                  ON card.`id` = goods.`base_id`
                  SET card.`active`=0
                  WHERE card.`updated`=0
                  AND goods.`ext_card_name`=:card',
                [
                    'card' => $extCard,
                ]
            );
        }
    }

    /**
     * Сброс пометки об обновленности товара.
     *
     * @param $extCard
     *
     * @throws \yii\db\Exception
     */
    public static function resetUpdated($extCard = null)
    {
        if ($extCard === null) {
            Query::SQL(
                'UPDATE `co_base_card` as card
                  INNER JOIN `c_goods` as goods
                  ON card.`id` = goods.`base_id`
                  SET card.`updated`=0
                  WHERE card.`updated`=1',
                []
            );
        } else {
            Query::SQL(
                'UPDATE `co_base_card` as card
                  INNER JOIN `c_goods` as goods
                  ON card.`id` = goods.`base_id`
                  SET card.`updated`=0
                  WHERE card.`updated`=1
                  AND goods.`ext_card_name`=:card',
                [
                    'card' => $extCard,
                ]
            );
        }
    }

    /**
     * принудительно исключаем из поискового индекса все неактивные товары.
     *
     * @throws \yii\db\Exception
     */
    public static function resetSearchAfterDeactivation()
    {
        Query::SQL(
            "UPDATE `search_index` as search
              INNER JOIN `co_base_card` as goods
              ON search.`object_id` = goods.`id`
              SET search.`status`=0
              WHERE search.`class_name`='CatalogViewer'
              AND goods.`active`=0"
        );
    }

    /**
     * Получить распарсенную каталожную позицию.
     *
     * @param int $id Id каталожной позиции
     * @param string $baseCard
     * @param bool $bAllFields Парсить все активные поля карточки(true) или только поля для быстрого просмотра(false)?
     * @param int $iCurrentSection - id текущего раздела. Если =0, вернет товар без seo - данных
     *
     * @throws \Exception
     * @throws ft\Exception
     *
     * @return array|bool
     */
    public static function getQuickView($id, $baseCard = Card::DEF_BASE_CARD, $bAllFields = false, $iCurrentSection = 0)
    {
        if (is_numeric($id)) {
            $oGoodsRow = GoodsRow::get($id, $baseCard);
        } else {
            $oGoodsRow = GoodsRow::getByAlias($id, $baseCard);
        }

        if (!$oGoodsRow) {
            return false;
        }

        $aGoodParams = model\GoodsTable::get($oGoodsRow->getRowId());

        $aGoodData = Parser::get($oGoodsRow->getFields(), $bAllFields ? ['active'] : ['active', 'show_in_quickview'])
            ->parseGood($aGoodParams, $oGoodsRow->getData(), false);

        return $aGoodData;
    }
}
