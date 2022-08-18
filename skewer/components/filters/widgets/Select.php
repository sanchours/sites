<?php

namespace skewer\components\filters\widgets;

use skewer\base\ft\Editor;
use skewer\components\catalog;
use skewer\components\catalog\cache\DictCache;
use skewer\components\catalog\Dict;
use skewer\components\filters;
use skewer\components\filters\IndexedFilter;
use skewer\components\gallery\Album;
use yii\helpers\ArrayHelper;

/**
 * Class Select - виджет select.
 */
class Select extends Prototype
{
    /** @var bool Флаг того, что в пунктах селекта будет выводится картинка */
    public $bWithImage = false;

    public function __construct(catalog\field\Prototype $oField, filters\FilterPrototype $oFilter = null)
    {
        parent::__construct($oField, $oFilter);

        if (in_array($this->oField->getType(), [Editor::SELECTIMAGE, Editor::MULTISELECTIMAGE])) {
            $this->bWithImage = true;
        }
    }

    public function parse($aFilterFieldData)
    {
        $items = $this->getDictField();

        if (!$items || count($items) == 1) {
            return [];
        }

        if ($items && count($items)) {
            foreach ($items as &$item) {
                if (self::equal($item['id'], $aFilterFieldData)) {
                    $item['check'] = true;
                }
            }
        }

        return [
            'title' => $this->getFieldTitle(),
            'name' => $this->getFieldName(),
            'type' => static::getTypeWidget(),
            'bWithImage' => $this->bWithImage,
            'items' => $items,
        ];
    }

    public static function getTypeWidget()
    {
        return 'select';
    }

    /**
     * Получение значений справочника для поля по фильтру.
     *
     * @return array|mixed
     */
    protected function getDictField()
    {
        $sField = $this->getFieldName();

        $oRel = $this->oField->getFtField()->getModel()->getOneFieldRelation($sField);

        if (!$oRel) {
            return [];
        }

        // find real id
        $arr = $this->oFilter->getUniqueValues4FilterField($this->oField);

        if (!$arr) {
            return [];
        }

        // find shadow id
        $clear_arr = $this->oFilter->getUniqueValues4FilterField($this->oField, true);

        // get list
        $aItems = Dict::getValues($oRel->getEntityName(), $arr, true);

        foreach ($aItems as &$aItem) {
            $bDisable = !in_array($aItem['id'], $clear_arr);
            $aItem['disable'] = !$this->oFilter->isShadowParams() ? false : $bDisable;

            if ($this->bWithImage && !empty($aItem['image'])) {
                $iAlbumId = $aItem['image'];
                $sPhoto = Album::getFirstActiveImage($iAlbumId, 'icon');
                if (file_exists(WEBPATH . $sPhoto)) {
                    $aItem['image'] = $sPhoto;
                } else {
                    $aItem['image'] = '';
                }
            } else {
                unset($aItem['image']);
            }
        }
        unset($aItem);

        if ($this->oFilter instanceof IndexedFilter) {
            foreach ($aItems as &$aItem) {
                $bDisable = !in_array($aItem['id'], $clear_arr);

                $aData4Url = $this->oFilter->buildData4FilterValue($this->getFieldName(), $aItem['id']);
                $sUrl = $this->oFilter->buildUrlByFilterData($aData4Url);
                $bCanIndex = $this->oFilter->canIndexUrlByFilterData($aData4Url);

                $aItem['url'] = $sUrl;
                $aItem['canIndex'] = $bCanIndex && !$bDisable;
            }
        }

        return $aItems;
    }

    /**
     * Метод сравнения двух переменных.
     *
     * @param int|string $val
     * @param mixed $tpl
     *
     * @return bool - true если $val либо равно либо является часть массива $tpl
     */
    private static function equal($val, $tpl)
    {
        if (is_array($tpl)) {
            return in_array($val, $tpl);
        }

        return $val == $tpl;
    }

    public function convertValueToAlias($aDataItem)
    {
        $aOut = $this->convertValue($aDataItem, 'id', 'alias');

        return $aOut;
    }

    public function convertValueToTitle($aDataItem)
    {
        $aOut = $this->convertValue($aDataItem, 'id', 'title');

        return $aOut;
    }

    public function convertValueToId($aDataItem)
    {
        $aOut = $this->convertValue($aDataItem, 'alias', 'id');

        return $aOut;
    }

    /**
     * Конвертировать значение поля.
     *
     * @param array $aDataItem - данные поля
     * @param string $sKeyField -  поле, используемое в качестве ключа
     * @param string $sValueField - поле, используемое в качестве значения
     *
     * @return array
     */
    protected function convertValue($aDataItem, $sKeyField, $sValueField)
    {
        $aOut = [];

        $iEntityId = $this->oField->getFtField()->getOption('link_id');
        $aDictData = DictCache::get($iEntityId);

        $aLinkArray = ArrayHelper::map($aDictData, $sKeyField, $sValueField);

        foreach ($aDataItem as $item) {
            if (isset($aLinkArray[$item])) {
                $aOut[] = $aLinkArray[$item];
            }
        }

        return $aOut;
    }

    /** {@inheritdoc} */
    public function filterInputVal(/* @noinspection PhpUnusedParameterInspection */ $aDataItem)
    {
        $aCountValues = array_count_values($aDataItem);

        if (!$aCountValues) {
            return false;
        }

        if ((count($aCountValues) == 1) && reset($aCountValues) && (key($aCountValues) == '')) {
            return false;
        }

        return true;
    }
}
