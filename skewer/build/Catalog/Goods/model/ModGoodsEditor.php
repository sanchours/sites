<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\base\ft;
use skewer\build\Catalog\Goods\Search;
use skewer\build\Page\CatalogMaps\Api;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\GoodsSelector;
use skewer\components\gallery;
use skewer\helpers\ImageResize;

/**
 * Модель для работы с товарной модификации
 * Class ModGoodsEditor.
 */
class ModGoodsEditor extends FormPrototype
{
    /** @var GoodsRow */
    private $oMainGoods;

    public static function get($iMainGoodsId)
    {
        $obj = new static();
        $obj->oMainGoods = GoodsRow::get($iMainGoodsId);

        return $obj;
    }

    /**
     * Получить id основного товара, для которого кастомизируется фильтр
     *
     * @return GoodsRow
     */
    public function getMainObject()
    {
        return $this->oMainGoods;
    }

    public function load($bGoNext = false)
    {
        // Если модификация существует, то получить её модель
        if ($id = $this->getDataField('id')) {
            $this->oGoodsRow = GoodsRow::get($id);

        // ... иначе создать модификацию на основе родительского товара
        } else {
            $aData = $this->oMainGoods->getData();
            $aDataModific = $this->getData();

            unset($aData['id']);

            // hack для галерей
            foreach ($this->oMainGoods->getFields() as $oFtField) {
                if ($oFtField->getEditorName() == ft\Editor::GALLERY) {
                    $sFieldName = $oFtField->getName();
                    if ($oFtField->getAttr('is_uniq')) {
                        // создаем новую (клонируем) галерею
                        if ($aData[$sFieldName]) {
                            $aData[$sFieldName] = gallery\Album::copyAlbum($aData[$sFieldName]);
                        }
                    }
                } elseif ($oFtField->getEditorName() == ft\Editor::MAP_SINGLE_MARKER) {
                    $sFieldName = $oFtField->getName();
                    if ($oFtField->getAttr('is_uniq')) {
                        // создаем новую (клонируем) карту
                        if ($aData[$sFieldName] && !isset($aDataModific[$sFieldName])) {
                            $aData[$sFieldName] = (string) Api::copyGeoObjectIdWithMap($aData[$sFieldName]);
                        }
                    }
                }
            }

            $this->oGoodsRow = GoodsRow::create($this->oMainGoods->getExtCardName());
            $this->oGoodsRow->setMainRowId($this->oMainGoods->getRowId());
            $this->oGoodsRow->setData($aData);
            // Убрать alias родительского товара при создании новой модификации
            $this->oGoodsRow->getBaseRow()->setVal('alias', '');
        }

        if ($this->type == 'form') {
            $aData = $this->data;

            // обработка полей по типу
            /** @var ft\model\Field[] $aFields */
            $aFields = array_intersect_key($this->oGoodsRow->getFields(), $aData);

            foreach ($aFields as $oField) {
                if ($oField->getEditorName() == ft\Editor::WYSWYG) {
                    $aData[$oField->getName()] = ImageResize::wrapTags($aData[$oField->getName()]);
                }

                if ($oField->getEditorName() == ft\Editor::MAP_SINGLE_MARKER) {
                    if (($sTmp = Api::extractGeoObjectIdFromAddress($aData[$oField->getName()])) !== false) {
                        $aData[$oField->getName()] = $sTmp;
                    }
                }
            }

            $this->oGoodsRow->setData($aData);
        } elseif ($this->type == 'field') {
            if (!$this->updField) {
                return false;
            }

            $aData[$this->updField] = $this->getDataField($this->updField);
            $this->oGoodsRow->setData($aData);
        } else {
            $this->data = $this->oGoodsRow->getData();

            return $bGoNext;
        }

        return true;
    }

    public function loadNewCopy($bGoNext = false)
    {
        // Если модификация существует, то получить её модель
        if ($id = $this->getDataField('id')) {
            $this->oGoodsRow = GoodsRow::get($id);

        // ... иначе создать модификацию на основе родительского товара
        } else {
            $aData = $this->data;
            unset($aData['id']);

            // hack для галерей
            foreach ($this->oMainGoods->getFields() as $oFtField) {
                if ($oFtField->getEditorName() == ft\Editor::GALLERY) {
                    $sFieldName = $oFtField->getName();
                    if ($oFtField->getAttr('is_uniq')) {
                        // создаем новую (клонируем) галерею
                        if ($aData[$sFieldName]) {
                            $aData[$sFieldName] = gallery\Album::copyAlbum($aData[$sFieldName]);
                        }
                    }
                } elseif ($oFtField->getEditorName() == ft\Editor::MAP_SINGLE_MARKER) {
                    $sFieldName = $oFtField->getName();
                    if ($oFtField->getAttr('is_uniq')) {
                        // создаем новую (клонируем) карту
                        if ($aData[$sFieldName]) {
                            $aData[$sFieldName] = (string) Api::copyGeoObjectIdWithMap($aData[$sFieldName]);
                        }
                    }
                }
            }
            $this->oGoodsRow = GoodsRow::create($this->oMainGoods->getExtCardName());

            $aFields = array_intersect_key($this->oGoodsRow->getFields(), $aData);
            foreach ($aFields as $oField) {
                if ($oField->getEditorName() == ft\Editor::WYSWYG) {
                    $aData[$oField->getName()] = ImageResize::wrapTags($aData[$oField->getName()]);
                }
            }

            $this->oGoodsRow->setMainRowId($this->oMainGoods->getRowId());
            $this->oGoodsRow->setData($aData);
        }

        return true;
    }

    public function save()
    {
        if (!$this->load()) {
            return false;
        }

        if (!($iId = $this->oGoodsRow->save())) {
            return false;
        }

        if (!($section = $this->oGoodsRow->getMainSection())) {
            $section = $this->oMainGoods->getMainSection();
            $this->oGoodsRow->setMainSection($section);
        }

        // обновление поискового индекса
        $oSearch = new Search();
        $oSearch->updateByObjectId($this->oGoodsRow->getRowId());

        return true;
    }

    public function saveNewCopy($iNewParent = null)
    {
        if ($iNewParent === null) {
            return false;
        }

        if (!$this->loadNewCopy(false)) {
            return false;
        }

        $this->oGoodsRow->setMainRowId($iNewParent);

        if (!($iId = $this->oGoodsRow->save())) {
            return false;
        }

        if (!($section = $this->oGoodsRow->getMainSection())) {
            $section = $this->oMainGoods->getMainSection();
            $this->oGoodsRow->setMainSection($section);
        }

        // обновление поискового индекса
        $oSearch = new Search();
        $oSearch->updateByObjectId($this->oGoodsRow->getRowId());

        return true;
    }

    public function delete()
    {
        if (!$this->load(true)) {
            return false;
        }

        if (!($id = $this->oGoodsRow->getRowId())) {
            return false;
        }

        if (!($iId = $this->oGoodsRow->delete())) {
            return false;
        }

        $oSearch = new Search();
        $oSearch->deleteByObjectId($id);

        return true;
    }

    /**
     * Удаление набора модификаий товаров.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function multipleDelete()
    {
        $aItems = [];
        if ($this->getDataField('multiple')) {
            if ($items = $this->getDataField('items')) {
                foreach ($items as $aItem) {
                    $aItems[] = $aItem['id'] ?? 0;
                }
            }
        } else {
            if ($id = $this->getDataField('id')) {
                $aItems[] = $id;
            }
        }

        if (count($aItems)) {
            foreach ($aItems as $id) {
                $this->oGoodsRow = GoodsRow::get($id);
                if (!$this->deleteGoods()) {
                    return false;
                }
            }
        }

        return true;
    }

    /** Удаление всех модификаций/аналогов товара $iGoodId */
    public static function multipleDeleteAll($iGoodId)
    {
        $oModel = new self();

        $oQuery = GoodsSelector::getModificationList($iGoodId);

        while ($aModGood = $oQuery->parseEach()) {
            $oModel->oGoodsRow = GoodsRow::get($aModGood['id']);
            $oModel->deleteGoods();
        }
    }

    private function deleteGoods()
    {
        if (!($id = $this->oGoodsRow->getRowId())) {
            return false;
        }

        $oSearch = new Search();
        $oSearch->deleteByObjectId($id);

        if (!($iId = $this->oGoodsRow->delete())) {
            return false;
        }

        return true;
    }
}
