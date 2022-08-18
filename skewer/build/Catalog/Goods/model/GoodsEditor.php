<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\build\Catalog\Goods\Search;
use skewer\build\Page\CatalogMaps\Api;
use skewer\build\Tool\Maps\YandexSettingsMap;
use skewer\components\catalog\Card;
use skewer\components\catalog\field\Prototype;
use skewer\components\catalog\GoodsRow;
use skewer\components\gallery;
use skewer\helpers\ImageResize;
use yii\base\ErrorException;
use yii\base\UserException;
use skewer\build\Page\CatalogMaps\Api as ApiCatalogMaps;

/**
 * Модель для работы с товаром
 * Class GoodsEditor.
 */
class GoodsEditor extends FormPrototype
{
    const categoryField = '__category';
    const mainLinkField = '__main_link';

    private $bExistRow = false;

    private $defSection = 0;

    private $defCard = '';

    private $defId = 0;

    public function setSectionData($iSectionId, $sSectionCard, $iDefGoods = 0)
    {
        $this->defSection = $iSectionId;
        $this->defCard = $sSectionCard;
        $this->defId = $iDefGoods;

        return $this;
    }

    public function isExistRow()
    {
        return $this->bExistRow;
    }

    public static function get()
    {
        $obj = new static();

        return $obj;
    }

    /**
     * Обработка входных данных.
     *
     * @param bool $bGoNext
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function load($bGoNext = false)
    {
        if (!$this->defId) {
            $this->defId = $this->getDataField('id');
        }

        if ($this->defId) {
            $this->oGoodsRow = GoodsRow::get($this->defId);
        } else {
            if ($sCard = $this->getDataField('__card')) {
                $this->defCard = $sCard;
            }

            // Проверить существование карточки и выдать более понятное предупреждение
            try {
                ft\Cache::get($this->defCard);
            } catch (\Exception $e) {
                throw new UserException(\Yii::t('catalog', 'error_card_not_found', $this->defCard));
            }

            $this->oGoodsRow = GoodsRow::create($this->defCard);
        }

        if ($this->type == 'form') {
            $aData = $this->getData();

            // обработка полей по типу
            $aFields = $this->oGoodsRow->getFields();
            foreach ($aFields as $oField) {
                if (isset($aData[$oField->getName()])) {
                    if ($oField->getEditorName() == ft\Editor::WYSWYG) {
                        $aData[$oField->getName()] = ImageResize::wrapTags($aData[$oField->getName()]);
                    }
                    //обработка гугл карты
                    if ($oField->getEditorName() == ft\Editor::MAP_SINGLE_MARKER) {
                        if (($sTmp = Api::extractGeoObjectIdFromAddress($aData[$oField->getName()])) !== false) {
                            $aData[$oField->getName()] = $sTmp;
                        }
                    }
                } else {
                    //обработка яндекс карты
                    if (ApiCatalogMaps::isActiveFieldYandexCard($oField)) {
                        $mapId = $this->oGoodsRow->getData()[$oField->getName()] ?: null;
                        $yandexMap = new YandexSettingsMap($mapId);
                        //если хотя бы один параметр карты был заполнен - то нужно заполнить все параметры карты
                        $leastOneParameterWasFilled = false;
                        foreach ($yandexMap->getAttributes() as $name => $dataValue) {
                            $dataValue = $aData["{$oField->getName()}_{$name}"];
                            $leastOneParameterWasFilled = $dataValue || $leastOneParameterWasFilled;
                            $yandexMap->{$name} = $dataValue;
                        }
                        if ($leastOneParameterWasFilled) {
                            $aData[$oField->getName()] = $yandexMap->save();
                        }
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
            return $bGoNext;
        }

        return true;
    }

    /**
     * Сохранение товара.
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->load()) {
            return false;
        }

        return $this->saveGoods();
    }

    private function saveGoods()
    {
        $aData = $this->oGoodsRow->getBaseRow()->getData();

        foreach ($this->oGoodsRow->getFields() as $key => $field) {
            if (isset($aData[$field->getName()])
                && class_exists(Prototype::getNamespace() . lcfirst($field->getDatatype()))
                && method_exists(Prototype::getNamespace() . lcfirst($field->getDatatype()), 'convertValueToNull')
                && call_user_func([Prototype::getNamespace() . lcfirst($field->getDatatype()), 'convertValueToNull'], $aData[$field->getName()])
            ) {
                $aData[$field->getName()] = 'null';
            }
        }

        $this->oGoodsRow->setData($aData);

        if (!$this->oGoodsRow->save()) {
            return false;
        }

        if ($sSectionList = $this->getDataField(self::categoryField)) {
            $aSectionList = $sSectionList ? explode(',', $sSectionList) : [];
            $this->oGoodsRow->setViewSection($aSectionList);
        } else {
            if (empty($aSectionList = $this->oGoodsRow->getViewSection())) {
                $aSectionList = [$this->defSection];
                $this->oGoodsRow->setViewSection($aSectionList);
            }
        }

        if ($iMainSection = $this->getDataField(self::mainLinkField)) {
            $this->oGoodsRow->setMainSection($iMainSection);
        }

        $this->oGoodsRow->getMainSection();

        // обновление поискового индекса
        $oSearch = new Search();
        $oSearch->updateByObjectId($this->oGoodsRow->getRowId());

        return true;
    }

    /**
     * Сохранить товар как новый.
     *
     * @return bool
     */
    public function saveNewCopy()
    {
        if (!$this->load(true)) {
            return false;
        }

        $aData = $this->oGoodsRow->getData();
        $aData['__card'] = $this->oGoodsRow->getExtCardName();

        unset($aData['id'] , $aData['alias'] , $aData[self::categoryField] , $aData[self::mainLinkField]);

        if (isset($aData['title'])) {
            while (Query::SelectFrom('co_' . Card::DEF_BASE_CARD)->where(['title' => $aData['title']])->getOne()) {
                $aData['title'] = $this->copyTitleChange($aData['title']);
            }
        }

        // дублирование всех галерей карточки
        $aFields = $this->oGoodsRow->getFields();
        foreach ($aFields as $oField) {
            if ($oField->getEditorName() == ft\Editor::GALLERY) {
                $aData[$oField->getName()] = gallery\Album::copyAlbum($aData[$oField->getName()]);
            } elseif ($oField->getEditorName() == ft\Editor::MAP_SINGLE_MARKER) {
                $aData[$oField->getName()] = Api::copyGeoObjectIdWithMap($aData[$oField->getName()]);
            }
        }

        //$aData[self::categoryField] = implode( ',', $this->oGoodsRow->getViewSection() );
        if (!$this->defSection) {
            return false;
        }

        // связываем с тем разделом, в котором находимся
        $aData[self::categoryField] = $this->defSection;

        $this->data = $aData;

        $this->oGoodsRow = GoodsRow::create($aData['__card']);
        $this->oGoodsRow->setData($aData);

        return $this->saveGoods();
    }

    private function copyTitleChange($sTitle)
    {
        $aMatches = [];
        $sPattern = '/\(' . \Yii::t('catalog', 'clone') . '-(\d+)\)/s';
        preg_match($sPattern, $sTitle, $aMatches);
        $sCopy = '(' . \Yii::t('catalog', 'clone') . ')';
        if (empty($aMatches)) {
            if (mb_strstr($sTitle, $sCopy)) {
                return str_replace('(' . \Yii::t('catalog', 'clone') . ')', '(' . \Yii::t('catalog', 'clone') . '-1' . ')', $sTitle);
            }

            return $sTitle . "  {$sCopy}";
        }

        $iNumber = (int) ($aMatches[1]) + 1;

        return preg_replace($sPattern, '(' . \Yii::t('catalog', 'clone') . '-${2}' . $iNumber . ')', $sTitle);
    }

    /**
     * Удаление товара.
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->load(true)) {
            return false;
        }

        return $this->deleteGoods();
    }

    private function deleteGoods()
    {
        if (!($id = $this->oGoodsRow->getRowId())) {
            return false;
        }

        $oSearch = new Search();
        $oSearch->deleteByObjectId($id);

        \Yii::$app->router->updateModificationDateSite();

        if (!($iId = $this->oGoodsRow->delete())) {
            return false;
        }

        return true;
    }

    /**
     * Удаление набора товаров.
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
                if (!$this->oGoodsRow instanceof GoodsRow || !$this->deleteGoods()) {
                    return false;
                }
            }
        }

        return true;
    }
}
