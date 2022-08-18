<?php

namespace skewer\components\filters;

use skewer\base\ft;
use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\components\catalog;
use skewer\components\seo;

class Api
{
    public static function getTypes()
    {
        $aTypes = [
            FilterPrototype::FILTER_TYPE_STANDARD => \Yii::t('catalog', 'filter_type_standard'),
        ];

        if (\Yii::$app->register->moduleExists('Filters', Layer::CATALOG)) {
            $aTypes[FilterPrototype::FILTER_TYPE_INDEX] = \Yii::t('catalog', 'filter_type_indexed');
        }

        return $aTypes;
    }

    /**
     * Определение типа виджита по полю каталога.
     *
     * @param catalog\field\Prototype $oField
     *
     * @return string
     */
    public static function getWidgetByCatalogField($oField)
    {
        if (!($oField instanceof FilteredInterface)) {
            return false;
        }

        $sWidget = $oField->getFilterWidgetName();

        return $sWidget;
    }

    /**
     * Получить тип фильтра.
     *
     * @return int */
    public static function getFilterType()
    {
        return SysVar::get('filter.filter_type');
    }

    /**
     * Получить описание seo-меток.
     *
     * @param mixed $mCard
     *
     * @return string
     */
    public static function getDescriptionSeoLabelsByCard($mCard)
    {
        $aOut = seo\Api::getDescriptionCommonLabels();

        /** @var widgets\Prototype[] $aFilterFields */
        $aFilterFields = self::getFilterFieldsByCard($mCard);

        foreach ($aFilterFields as $oFilterField) {
            if ($oFilterField->canHaveTitle()) {
                $aOut[] = sprintf('[%s]', $oFilterField->getFieldTitle());
            }
        }

        return implode('<br>', $aOut);
    }

    /**
     * Вернёт массив полей фильтра по карточке.
     *
     * @param mixed $mCard - карточка
     * @param FilterPrototype | null $oFilterInstance - объект фильтра
     *
     * @return widgets\Prototype[]
     */
    public static function getFilterFieldsByCard($mCard, FilterPrototype $oFilterInstance = null)
    {
        $aFilterFields = [];

        $oModel = ft\Cache::get($mCard);

        $aFields = $oModel->getFileds();

        if ($oModel->getType() == catalog\Card::TypeExtended) {
            $oParModel = ft\Cache::get($oModel->getParentId());
            $aFields = array_merge($aFields, $oParModel->getFileds());
        }

        foreach ($aFields as $oField) {
            if ($oField->getAttr(catalog\Attr::SHOW_IN_FILTER) and $oField->getAttr(catalog\Attr::ACTIVE)) {
                $oCatalogField = catalog\field\Prototype::init($oField);

                if ($oFilterField = widgets\Prototype::getInstanceWidget($oCatalogField, $oFilterInstance)) {
                    $aFilterFields[$oField->getName()] = $oFilterField;
                }
            }
        }

        return $aFilterFields;
    }
}
