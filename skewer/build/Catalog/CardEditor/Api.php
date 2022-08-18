<?php

namespace skewer\build\Catalog\CardEditor;

use skewer\base\ft;
use skewer\base\site\Layer;
use skewer\build\Cms\EditorMap;
use skewer\build\Page\CatalogMaps;
use skewer\build\Tool\Payments\Api as PaymentsApi;
use skewer\components\catalog;
use skewer\components\import\Task;

/**
 * API для редактора карточек
 * Class Api.
 */
class Api
{
    const TypeSubEntity = 'sub_entity';

    /**
     * Кэш имен групп для спискового интерфейса.
     *
     * @var array
     */
    private static $aGroupWidgetList = [];

    /**
     * Форматированный список базовых карточек.
     *
     * @param int $id Идентификатор карты, которую исплючаем из списка
     *
     * @return array
     */
    public static function getBasicCardList($id = 0)
    {
        $aList = [];

        $query = catalog\model\EntityTable::find()
            ->where('module', catalog\Card::ModuleName)
            ->where('type', catalog\Card::TypeBasic)
            ->where('parent', '0');

        /** @var catalog\model\EntityRow $card */
        while ($card = $query->each()) {
            if ($card->id != $id) {
                $aList[$card->id] = $card->title;
            }
        }

        return $aList;
    }

    /**
     * Отдает имя группы для спискового интерфейса.
     *
     * @param catalog\model\FieldRow $oItem
     * @param string $sField
     *
     * @return string
     * Используется как метод для виджитирования полей
     */
    public static function applyGroupWidget(catalog\model\FieldRow $oItem, $sField)
    {
        $iId = $oItem->{$sField};

        if (isset(self::$aGroupWidgetList[$iId])) {
            return self::$aGroupWidgetList[$iId];
        }

        $oGroup = catalog\Card::getGroup($iId);

        if ($oGroup && $oGroup->id) {
            return sprintf('[%02d] %s', $oGroup->position, $oGroup->title);
        }

        return '[00] ' . \Yii::t('card', 'base_group');
    }

    /**
     * отдает имя редактора.
     *
     * @param $oItem
     * @param $sField
     *
     * @return string
     * Используется как метод для виджитирования полей
     */
    public static function applyEditorWidget($oItem, $sField)
    {
        $aEditorList = self::getSimpleTypeList();

        if (isset($aEditorList[$oItem->{$sField}])) {
            return $aEditorList[$oItem->{$sField}];
        }

        return $oItem->{$sField};
    }

    /**
     * отдает метку для типа карточки.
     *
     * @param $oItem
     * @param $sField
     *
     * @return mixed
     * Используется как метод для виджитирования полей
     */
    public static function applyTypeWidget($oItem, $sField)
    {
        $aTypeList = ['Dict', 'Base', 'Ext'];

        if (isset($aTypeList[$oItem->{$sField}])) {
            return $aTypeList[$oItem->{$sField}];
        }

        return $oItem->{$sField};
    }

    /**
     * Отдает набор типов для поля.
     *
     * @param bool $bAddLinkFields Флаг добавления в список типов для связных сущностей
     *
     * @return array
     */
    public static function getSimpleTypeList($bAddLinkFields = true)
    {
        $aList = ft\Editor::getSimpleList();

        $aList[ft\Editor::SELECT] = \Yii::t('catalog', 'field_f_sub_link');
        $aList[ft\Editor::PAYMENTOBJECT] = \Yii::t('catalog', 'field_f_payment_object');

        if ($bAddLinkFields) {
            $aList[ft\Editor::MULTISELECT] = \Yii::t('catalog', 'field_f_multisub_link');
            $aList[ft\Editor::SELECTIMAGE] = \Yii::t('catalog', 'field_f_subimage_link');
            $aList[ft\Editor::MULTISELECTIMAGE] = \Yii::t('catalog', 'field_f_multisubimage_link');

            if (\Yii::$app->register->moduleExists('Collections', Layer::CATALOG)) {
                $aList[ft\Editor::COLLECTION] = \Yii::t('catalog', 'field_f_brands');
                $aList[ft\Editor::MULTICOLLECTION] = \Yii::t('catalog', 'field_f_multi_brands');
            }

            if (\Yii::$app->register->moduleExists(CatalogMaps\Module::getNameModule(), Layer::PAGE) &&
                 \Yii::$app->register->moduleExists(EditorMap\Module::getNameModule(), Layer::CMS)) {
                $aList[ft\Editor::MAP_SINGLE_MARKER] = \Yii::t('catalog', 'field_f_' . ft\Editor::MAP_SINGLE_MARKER);
            }
        }
        asort($aList);

        return $aList;
    }

    /**
     * Список сущностей для связи с полем
     *
     * @param catalog\model\FieldRow $oField
     *
     * @return array
     */
    public static function getEntityList($oField)
    {
        $sNameFieldClass = catalog\Api::getClassField($oField->editor);
        if ($sNameFieldClass) {
            /** @var catalog\field\Prototype $sNameFieldClass */
            $aOut = $sNameFieldClass::getEntityList($oField->link_id);
        }

        return $aOut ?? [];
    }

    /**
     * @param catalog\model\FieldRow $oField Объект поля
     *
     * @return array
     */
    public static function getSimpleWidgetList($oField)
    {
        $sNameFieldClass = catalog\Api::getClassField($oField->editor);
        if ($sNameFieldClass) {
            /** @var catalog\field\Prototype $oProtField */
            $oProtField = new $sNameFieldClass();
            $aWidgetList = $oProtField::getGroupWidgetList($oField->link_id);
        }

        return $aWidgetList ?? [];
    }

    /**
     * Getter скрытых полей в админке.
     *
     * @return array
     */
    public static function getHiddenFields()
    {
        return [
            Task::$sHashFieldName,
            Task::$sUpdatedFieldName,
        ];
    }

    /**
     * @return array
     */
    public static function getTitlePaymentObject()
    {
        foreach (PaymentsApi::$aPaymentObject as $item) {
            $aItems[$item] = \Yii::t('card', $item);
        }

        return $aItems;
    }
}
