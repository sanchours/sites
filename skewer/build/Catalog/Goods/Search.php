<?php

namespace skewer\build\Catalog\Goods;

use skewer\base\ft\Cache;
use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\base\SysVar;
use skewer\components\catalog;
use skewer\components\catalog\Parser;
use skewer\components\search\Api;
use skewer\components\search\Prototype;

/** @property catalog\GoodsRow $oEntity */
class Search extends Prototype
{
    const CLASS_NAME = 'CatalogViewer';

    /** @var bool Включены товары-модификации? */
    protected $bIsGoodsModificationEnable;

    /**
     * @__construct
     */
    public function __construct()
    {
        $this->bIsGoodsModificationEnable = (bool) SysVar::get('catalog.goods_modifications');
    }

    /**
     * Установить объект товара.
     *
     * @param catalog\GoodsRow $oGood
     */
    public function setEntity(catalog\GoodsRow $oGood)
    {
        $this->oEntity = $oGood;
    }

    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    public function getName()
    {
        return self::CLASS_NAME;
    }

    /** {@inheritdoc} */
    protected function grabEntity()
    {
        return (!$this->oEntity) ? $this->grabEntityFromDb() : $this->oEntity;
    }

    /**
     * Запросить сущность из бд.
     *
     * @return catalog\GoodsRow
     */
    protected function grabEntityFromDb()
    {
        return catalog\GoodsRow::get($this->oSearchIndexRow->object_id);
    }

    /** {@inheritdoc} */
    protected function getNewSectionId()
    {
        // если появится множество базовых карточек, то можно применить подход с
        //      вычислением базовой карточки как в коллекциях
        $iBaseCard = Cache::get(catalog\Card::DEF_BASE_CARD)->getEntityId();

        // Если у товара слетел базовый раздел (например удалён), то здесь произведётся попытка установить новый
        return catalog\Section::getMain4Goods($this->oSearchIndexRow->object_id, $iBaseCard);
    }

    /** {@inheritdoc} */
    protected function checkEntity()
    {
        if (!$this->oEntity) {
            return false;
        }

        if (!$this->oEntity->getBaseRow()->getVal('active')) {
            return false;
        }

        if (!$this->bIsGoodsModificationEnable) {
            if ($this->isModificationGood()) {
                return false;
            }
        }

        return true;
    }

    /** {@inheritdoc} */
    protected function fillSearchRow()
    {
        $sText = $this->collectGoodData($this->oEntity);

        $this->oSearchIndexRow->language = Parameters::getLanguage($this->oSearchIndexRow->section_id);
        $this->oSearchIndexRow->text = $this->stripTags($sText);
        $this->oSearchIndexRow->search_text = $this->stripTags(sprintf('%s %s', $this->getIndexedByTwoSymbolsData($this->oEntity), $sText));
        $this->oSearchIndexRow->search_title = $this->stripTags($this->oEntity->getBaseRow()->getVal('title'));
        $this->oSearchIndexRow->href = $this->buildHrefSearchIndexRow();

        if (catalog\Card::isDetailHidden($this->oSearchIndexRow->section_id)) {
            $this->oSearchIndexRow->use_in_sitemap = false;
        }

        $oSeoComponent = new SeoGood($this->oSearchIndexRow->object_id, $this->oSearchIndexRow->section_id);
        $this->fillSearchRowSeoData($this->oSearchIndexRow, $oSeoComponent);

        $iBaseCard = Cache::get(catalog\Card::DEF_BASE_CARD)->getEntityId();
        $this->oSearchIndexRow->modify_date = catalog\model\GoodsTable::getChangeDate($this->oSearchIndexRow->object_id, $iBaseCard);
    }

    /** {@inheritdoc} */
    protected function buildHrefSearchIndexRow()
    {
        return Parser::buildUrl($this->oSearchIndexRow->section_id, $this->oSearchIndexRow->object_id, $this->oEntity->getBaseRow()->getVal('alias'));
    }

    /**
     * Данные товара заиндексированные по 2м первым символам
     *
     * @param catalog\GoodsRow $oGoodsRow - объект товара, содер-й базовую и расширен. карточки
     *
     * @return string
     */
    private function getIndexedByTwoSymbolsData(catalog\GoodsRow $oGoodsRow)
    {
        return Api::indexFirstCharsString($oGoodsRow->getBaseRow()->getVal('article'));
    }

    /**
     * Собирает данные с товара для поискового индекса.
     *
     * @param catalog\GoodsRow $oGoodsRow - объект товара, содер-й базовую и расширен. карточки
     *
     * @return string $aSearchData
     */
    private function collectGoodData(catalog\GoodsRow $oGoodsRow)
    {
        $aSearchData = '';

        // Обязательные для включения поля, включаются в поиск независимо от атрибута 'show_in_search'
        $aRequiredFields = ['article'];

        $aFields = $oGoodsRow->getFields();

        if ($oGoodsRow) {
            $aGoods = $oGoodsRow->getData();
            foreach ($aFields as $sName => $aField) {
                $aAttr = $aField->getAttrs();

                if (!empty($aGoods[$sName]) && (in_array($sName, $aRequiredFields) || !empty($aAttr['show_in_search']))) {
                    $iLinkId = $aField->getOption('link_id');

                    if (!$iLinkId) {
                        //поле незалинковано ни с какой сущностью. Стандартное добавление
                        $aSearchData .= ($aGoods[$sName]) ? ' ' . $aGoods[$sName] : '';
                    } else {
                        //поле залинковано с какой то сущностью
                        $aData = catalog\Dict::getValById($iLinkId, $aGoods[$sName]);

                        foreach ($aData as $aItem) {
                            /*В поисковую строку забираем только title. Возможно потом понадобится еще что то забрать*/
                            $aSearchData .= ' ' . $this->stripTags($aItem['title']);
                        }
                    }
                }
            }
        }

        $aSearchData = $this->stripTags($aSearchData);
        $aSearchData = trim($aSearchData);

        return $aSearchData;
    }

    /**
     * Товар является модификацией?
     *
     * @return bool
     */
    protected function isModificationGood()
    {
        $bIsModifications = (bool) catalog\model\GoodsTable::find()
            ->where('base_id', $this->oSearchIndexRow->object_id)
            ->where('parent !=?', $this->oSearchIndexRow->object_id)
            ->asArray()
            ->getOne();

        return $bIsModifications;
    }

    /**
     *  воссоздает полный список пустых записей для сущности, отдает количество добавленных.
     */
    public function restore()
    {
        $sql = "INSERT INTO search_index(`status`,`class_name`,`object_id`)  SELECT '0','{$this->getName()}',base_id  FROM c_goods WHERE 1";
        Query::SQL($sql);
    }

    public static function rebuildSearchByCardName($sCardName, $sValue)
    {
        $sNameCard = ($sCardName == catalog\Card::DEF_BASE_CARD) ? "co_{$sCardName}" : "ce_{$sCardName}";
        \Yii::$app->db->createCommand("UPDATE search_index
                                        SET
                                        use_in_sitemap = '{$sValue}'
                                        where object_id in
                                        (
                                          select id from
                                          {$sNameCard}
                                        )")->execute();
    }
}
