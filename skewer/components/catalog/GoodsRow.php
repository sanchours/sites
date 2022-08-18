<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\orm\ActiveRecord;
use skewer\base\orm\Query;
use skewer\base\SysVar;
use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\build\Catalog\Goods\Exporter;
use skewer\build\Catalog\Goods\Importer;
use skewer\build\Catalog\Goods\model\GoodsEditor;
use skewer\build\Catalog\Goods\model\ModGoodsEditor;
use skewer\build\Catalog\Goods\Search;
use skewer\build\Page\CatalogMaps\models\GeoObjects;
use skewer\build\Page\CatalogMaps\models\Maps;
use skewer\build\Page\CatalogViewer;
use skewer\build\Page\WishList\WishList;
use skewer\build\Page\WishList\WishListEvent;
use skewer\build\Tool\SeoGen\exporter\GetListExportersEvent;
use skewer\build\Tool\SeoGen\importer\GetListImportersEvent;
use skewer\components\catalog;
use skewer\components\gallery\Album;
use skewer\components\import\field\Section as ImportSection;
use skewer\components\rating\Rating;
use skewer\helpers\Transliterate;
use yii\base\ModelEvent;

/**
 * класс для работы с товаром, состоящим из нескольких карточек.
 */
class GoodsRow extends GoodsRowPrototype
{
    /**
     * Создание нового товара.
     *
     * @param $card
     *
     * @throws ft\Exception
     *
     * @return GoodsRow
     */
    public static function create($card)
    {
        $oGoodsRow = new self();

        $sBaseCard = Card::getBaseCard($card);

        // base row
        $oBaseModel = ft\Cache::get($sBaseCard);
        $oBaseRow = ActiveRecord::getByFTModel($oBaseModel);

        $oGoodsRow->setBaseRow($oBaseRow);
        $oGoodsRow->setBaseCardName($sBaseCard);

        // ext row
        $oExtModel = ft\Cache::get($card);
        if ($oExtModel->getType() != Entity::TypeExtended) {
            throw new ft\Exception('ExtCard no extended type');
        }
        $oExtRow = ActiveRecord::getByFTModel($oExtModel);

        $oGoodsRow->setExtRow($oExtRow);
        $oGoodsRow->setExtCardName($card);

        return $oGoodsRow;
    }

    /**
     * Получить объект товара по id.
     *
     * @param int $id
     * @param int|string $card
     * @param mixed $mBaseCard
     *
     * @throws \Exception
     * @throws ft\Exception
     *
     * @return bool|GoodsRow
     */
    public static function get($id, /** @noinspection PhpUnusedParameterInspection */
                               $mBaseCard = '')
    {
        $oGoodsRow = new self();

        if (!($row = catalog\model\GoodsTable::get($id))) {
            return false;
        }

        $oModel = ft\Cache::get($row['base_card_name']);

        if (!$oModel) {
            throw new \Exception('Не найдена модель для карточки.');
        }
        $oBaseRow = Query::SelectFrom($oModel->getTableName(), $oModel)
            ->where($oModel->getPrimaryKey(), $id)
            ->getOne();

        $oGoodsRow->setRowId($id);
        $oGoodsRow->setBaseRow($oBaseRow);
        $oGoodsRow->setBaseCardName($row['base_card_name']);

        // ext row
        $oExtModel = ft\Cache::get($row['ext_card_name']);
        $oExtRow = Query::SelectFrom($oExtModel->getTableName(), $oExtModel)
            ->where($oBaseRow->primaryKey(), $oBaseRow->getPrimaryKeyValue())
            ->getOne();

        $oGoodsRow->setExtRow($oExtRow);
        $oGoodsRow->setExtCardName($row['ext_card_name']);

        $oGoodsRow->setMainRowId($row['parent']);

        $oGoodsRow->fillLinkFields();

        return $oGoodsRow;
    }

    /**
     * @param $alias
     * @param int|string $mBaseCard id или name базовой карточки, но сработрает и для расширенной
     *
     * @throws \Exception
     *
     * @return bool|GoodsRow
     */
    public static function getByAlias($alias, $mBaseCard = '')
    {
        if (!($oModel = ft\Cache::get($mBaseCard))) {
            throw new \Exception('Не найдена модель для карточки.');
        }
        if ($oModel->getType() != Entity::TypeBasic && $oModel->getParentId()) {
            if (!($oModel = ft\Cache::get($oModel->getParentId()))) {
                throw new \Exception('Не найдена модель для карточки.');
            }
        }
        if (!($oBaseRow = Card::getItemRow($oModel->getName(), ['alias' => $alias]))) {
            return false;
        }

        return self::get($oBaseRow->getPrimaryKeyValue(), $oModel->getName());
    }

    public static function getByFields($aFields, $card = '')
    {
        if (!($oModel = ft\Cache::get($card))) {
            throw new \Exception('Не найдена модель для карточки.');
        }
        if (!($oBaseRow = Card::getItemRow($card, $aFields))) {
            return false;
        }

        return self::get($oBaseRow->getPrimaryKeyValue(), $card);
    }

    /**
     * Изменение значений полей товара.
     *
     * @param array $aData
     */
    public function setData($aData)
    {
        //Если нет алиаса но есть title
        if (isset($aData['title']) && !isset($aData['alias'])) {
            $aData['alias'] = $aData['title'];
        }

        // генерация и сохранение алиаса
        if ($this->oBaseRow->getModel()->hasField('alias') && isset($aData['alias'])) {
            if ($this->getRowId()) {
                $aData['id'] = $this->getRowId();
            }
            $this->checkAlias($aData);
        }

        // перебираем все пришедшие данные
        foreach ($aData as $sKey => $mVal) {
            $this->setField($sKey, $mVal);
        }
    }

    /**
     * Установить значение поля товара.
     *
     * @param string $sFieldName - имя поля
     * @param mixed $mVal - значение поля
     */
    public function setField($sFieldName, $mVal)
    {
        if ($this->oBaseRow->getModel()->hasField($sFieldName)) {
            $this->oBaseRow->{$sFieldName} = $mVal;
        }

        if ($this->oExtRow and $this->oExtRow->getModel()->hasField($sFieldName)) {
            $this->oExtRow->{$sFieldName} = $mVal;
        }
    }

    /**
     * Получение значений полей товара
     * Данные базвовой как есть,.
     *
     * @return array
     */
    public function getData()
    {
        $aData = [];

        if ($this->hasBaseRow()) {
            $aData = $this->getBaseRow()->getData();
        }

        if ($this->hasExtRow()) {
            foreach ($this->getExtRow()->getData() as $sKey => $mVal) {
                $aData[$sKey] = $mVal;
            }
        }

        return $aData;
    }

    /**
     * Список разделов для показа.
     *
     * @return array|bool
     */
    public function getViewSection()
    {
        return Section::getList4Goods($this->oBaseRow->getPrimaryKeyValue(), $this->getBaseCardId());
    }

    /**
     * Актуализация связей товара с разделами и возвращает основной раздел.
     *
     * @param array $aSectionList
     *
     * @return bool|int
     */
    public function setViewSection($aSectionList = [])
    {
        Section::save4Goods($this->getRowId(), $this->getBaseCardId(), $this->getExtCardId(), $aSectionList);

        return $this->getMainSection();
    }

    /**
     * Получение/установка основного раздела для товара.
     *
     * @return bool|int
     */
    public function getMainSection()
    {
        return Section::getMain4Goods($this->getRowId(), $this->getBaseCardId());
    }

    /**
     * Обновление главного раздела для товара.
     *
     * @param $iSectionId
     *
     * @return bool
     */
    public function setMainSection($iSectionId)
    {
        return catalog\model\GoodsTable::setMainSection($this->getRowId(), $iSectionId);
    }

    /**
     * Сохранение товара.
     *
     * @return bool
     */
    public function save()
    {
        $bNew = !$this->iRowId;

        if (!parent::save()) {
            return false;
        }

        if ($bNew) {
            $this->createLink();
        } else {
            if ($this->wasUpdated()) {
                $this->setUpdDate();
            }

            if (SysVar::get('catalog.goods_modifications') && $this->isMainRow()) {
                // для модификаций сохраняем не уникальные поля
                $data = [];
                $values = $this->getData();
                unset($values['id']);
                foreach ($this->getFields() as $oFtField) {
                    $sFieldName = $oFtField->getName();
                    if (!$oFtField->getAttr('is_uniq') && isset($values[$sFieldName])) {
                        $data[$sFieldName] = $values[$sFieldName];
                    }
                }

                if (count($data)) {
                    $list = catalog\GoodsSelector::getModificationList($this->getRowId())->getArray($count);
                    foreach ($list as $row) {
                        $goods = self::get($row['id']);

                        /*Если для модификации уже задан алиас, используем его*/
                        if (isset($row['alias'])) {
                            $data['alias'] = $row['alias'];
                        }

                        $goods->setData($data);
                        $goods->save();
                        // обновление поискового индекса
                        $oSearch = new Search();
                        $oSearch->setEntity($goods);
                        $oSearch->updateByObjectId($row['id']);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Удаление товара.
     */
    public function delete()
    {
        if (!$this->iRowId) {
            return false;
        }

        if (!parent::delete()) {
            return false;
        }

        // Удаление модификаций
        if ($this->isMainRow()) {
            ModGoodsEditor::multipleDeleteAll($this->iRowId);
        }

        /* Удаление галерей товара */
        $aData = $this->getData();
        foreach ($this->getFields() as $sFieldName => $oField) {
            // Если товар не модификация или у модификации своя галерея (ps модификация может иметь одну галерею с товаром)
            if ($this->isMainRow() or $oField->getAttr('is_uniq')) {
                if ($oField->getEditorName() == ft\Editor::GALLERY) {
                    $iAlbumId = $aData[$sFieldName] ?? 0;
                    $iAlbumId and Album::removeAlbum($iAlbumId, $error);
                }
                if ($oField->getEditorName() == ft\Editor::MAP_SINGLE_MARKER) {
                    $iGeoObjectId = $aData[$sFieldName] ?? 0;
                    $iMapId = GeoObjects::getMapByGeoObjectId($iGeoObjectId);
                    $iGeoObjectId and GeoObjects::deleteAll(['id' => $iGeoObjectId]);
                    $iMapId and Maps::deleteAll(['id' => $iMapId]);
                }
            }
        }

        // Удаление рейтинга к товару
        (new Rating(CatalogViewer\Module::getNameModule()))->removeRating($this->iRowId);

        // Удалить отзывы к товару
        GuestBook::removeReviews4Good($this->iRowId);

        catalog\model\SemanticTable::remove($this->iRowId, $this->getBaseCardId());
        $this->setViewSection([]);
        $this->removeLink();
        if (WishList::isModuleOn()) {
            /** Удаление связей с Отложенными товарами */
            $oWishListEvent = new WishListEvent();
            $oWishListEvent->aData = $aData;
            $oWishListEvent->extCardName = $this->getExtCardName();
            $oWishListEvent->iGoodsId = $this->iRowId;
            $oWishListEvent->bRemoveGoods = true;
            $oWishListEvent->removeOldWishes();
        }

        return true;
    }

    private function setUpdDate()
    {
        catalog\model\GoodsTable::setChangeDate($this->iRowId, $this->getBaseCardId());
    }

    private function createLink()
    {
        $priority = 0;
        if (!$this->isMainRow()) {
            $priority = Api::getMaxPriority($this->getMainRowId());
        }
        catalog\model\GoodsTable::add(
            $this->iRowId,
            $this->getBaseCardId(),
            $this->getBaseCardName(),
            $this->getExtCardId(),
            $this->getExtCardName(),
            $this->isMainRow() ? $this->getRowId() : $this->getMainRowId(),
            0,
            $priority
        );
    }

    private function removeLink()
    {
        catalog\model\GoodsTable::remove($this->iRowId, $this->getBaseCardId());
    }

    #63792_generate_alias
    protected function checkAlias(&$aData)
    {
        $sAlias = $aData['alias'] ?? $this->getBaseRow()->getVal('alias');
        $sTitle = $aData['title'] ?? $this->getBaseRow()->getVal('title');

        if ($this->iRowId) {
            $sOldTitle = $this->getBaseRow()->getVal('title');
            $sOldAlias = $this->getBaseRow()->getVal('alias');
            // Alias должен создаваться заново при следующих условиях:
            // 1. title изменен
            // 2. alias задан и не изменен при редактировании и не изменен при сохранении
            // 3. старый title при транслитерации совпадает с alias
            if ($sOldTitle != $sTitle && $sOldAlias == $sAlias) {
                $oldAlias = Transliterate::generateAlias($sOldTitle);
                if ($sOldAlias == $oldAlias || (mb_strpos($sOldAlias, $oldAlias) !== false)) {
                    $sAlias = $sTitle;
                }
            }
        }

        if (!$sAlias) {
            $sAlias = $sTitle;
        }

        $sAlias = Transliterate::generateAlias($sAlias);

        if (isset($aData[GoodsEditor::mainLinkField])) {
            /*Стандартное сохранение*/
            $sAlias = \skewer\components\seo\Service::generateAlias($sAlias, $aData['id'], $aData[GoodsEditor::mainLinkField], 'CatalogViewer');
        } elseif (ImportSection::$iCurrentSection) {
            /*Сохранение через импорт*/
            $sAlias = \skewer\components\seo\Service::generateAlias($sAlias, $aData['id'] ?? 0, ImportSection::$iCurrentSection, 'CatalogViewer');
        } elseif (isset($aData[GoodsEditor::categoryField])) {
            /*Не импортный товар и не установлен основной раздел*/
            /*Выдернем категорию в которую он пишется*/
            $aCategories = explode(',', $aData[GoodsEditor::categoryField]);
            $sAlias = \skewer\components\seo\Service::generateAlias($sAlias, 0, $aCategories[0], 'CatalogViewer');
        }

        $aData['alias'] = $this->getUniqAlias($sAlias);
    }

    #63792_generate_alias
    protected function getUniqAlias($sAlias)
    {
        $flag = (bool) Query::SelectFrom($this->getBaseRow()->getModel()->getTableName())
            ->where('alias', $sAlias)
            ->andWhere('id!=?', $this->iRowId)
            ->getCount();

        if (!$flag) {
            return $sAlias;
        }

        preg_match('/^(\S+)(-\d+)?$/Uis', $sAlias, $res);
        $sTplAlias = $res[1] ?? $sAlias;
        $iCnt = isset($res[2]) ? -(int) $res[2] : 0;
        while (mb_substr($sTplAlias, -1) == '-') {
            $sTplAlias = mb_substr($sTplAlias, 0, mb_strlen($sTplAlias) - 1);
        }

        do {
            ++$iCnt;
            $sAlias = $sTplAlias . '-' . $iCnt;

            $flag = (bool) Query::SelectFrom($this->getBaseRow()->getModel()->getTableName())
                ->where('alias', $sAlias)
                ->andWhere('id!=?', $this->iRowId)
                ->getCount();
        } while ($flag);

        return $sAlias;
    }

    /**
     * Получение значений для сложно связанных полей.
     */
    protected function fillLinkFields()
    {
        foreach ($this->getFields() as $oField) {
            $aRel = $oField->getRelationList();

            if (count($aRel)) {
                foreach ($aRel as $oRel) {
                    if ($oRel->getType() == ft\Relation::MANY_TO_MANY) {
                        $sField = $oField->getName();
                        $this->setData([$sField => $oField->getLinkRow($this->getRowId())]);
                    }
                }
            }
        }
    }

    /**
     * Уладение всех связей товаров с разделом $iSectionId.
     *
     * @param ModelEvent $event
     */
    public static function removeSection(ModelEvent $event)
    {
        model\SectionTable::unlink($event->sender->id);
        model\GoodsTable::removeSection($event->sender->id);
    }

    /**
     * Класс для сборки списка автивных поисковых движков.
     *
     * @param \skewer\components\search\GetEngineEvent $event
     */
    public static function getSearchEngine(\skewer\components\search\GetEngineEvent $event)
    {
        $event->addSearchEngine(Search::className());
    }

    /**
     * Регистрирует класс Importer, в списке импортёров события $oEvent.
     *
     * @param GetListImportersEvent $oEvent
     */
    public static function getImporter(GetListImportersEvent $oEvent)
    {
        $oEvent->addImporter(Importer::className());
    }

    /**
     * Регистрирует класс Exporter, в списке экпортёров события $oEvent.
     *
     * @param GetListExportersEvent $oEvent
     */
    public static function getExporter(GetListExportersEvent $oEvent)
    {
        $oEvent->addExporter(Exporter::className());
    }

    /**
     * @return string
     */
    public function getPaymentObject()
    {
        $aData = $this->getData();

        if (isset($aData['payment_object']) && $aData['payment_object']) {
            return $aData['payment_object'];
        }

        $paymentObject = SysVar::get(Card::PREFIX_PAYMENT_OBJECT_NAME . $this->sExtCardName);
        if ($paymentObject) {
            return $paymentObject;
        }

        return Card::getPaymentObject();
    }
}
