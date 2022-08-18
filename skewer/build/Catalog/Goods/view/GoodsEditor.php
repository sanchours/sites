<?php

namespace skewer\build\Catalog\Goods\view;

use skewer\base\ft;
use skewer\base\section\Tree;
use skewer\base\SysVar;
use skewer\base\Twig;
use skewer\base\ui\builder\FormBuilder;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Page\CatalogMaps;
use skewer\build\Tool\Maps\YandexSettingsMap;
use skewer\components\catalog;
use skewer\components\ext;
use skewer\components\gallery\Profile;
use skewer\components\seo\Api;
use skewer\build\Page\CatalogMaps\Api as ApiCatalogMaps;

/**
 * Построение интерфейса редактирование товара
 * Class GoodsEditor.
 */
class GoodsEditor extends FormPrototype
{
    const categoryField = '__category';
    const mainLinkField = '__main_link';

    const FIELD_COORDINATES = 'coordinates';

    const FIELDS_FOR_REPLACE_YANDEX_MAP = [
        self::FIELD_COORDINATES,
        'center',
        'zoom',
        'address',
    ];

    public static $bOnlyActive = false;

    /**
     * @throws \Exception
     */
    public function build()
    {
        // Задание набора полей для интерфейса
        $fields = $this->initForm();

        /** @var catalog\GoodsRow $oGoodsRow */
        $oGoodsRow = $this->model->getGoodsRow();

        // Флаг существования каталожной позиции
        $bGoodsExists = $oGoodsRow->getRowId();

        /* Кнопки */

        $this->_form->getForm()->addBtnSave('edit', 'edit');
        $this->_form->buttonCancel();

        /*Фильтр по "только активные"*/
        $this->filterByActive($fields, $bGoodsExists);
        $this->filterByVisible();

        // Если редактируется существующая каталожная позиция
        if ($bGoodsExists) {
            $this->_form->buttonSeparator();
            $this->btnShowRelatedItems();
            $this->btnShowIncludedItems();
            $this->btnShowModificationsItems();

            if (SysVar::get('catalog.goods_include') || SysVar::get('catalog.goods_related')) {
                $this->_form->buttonSeparator();
            }

            $this->_form->buttonDelete();
        }

        $this->_form->useSpecSectionForImages();

        $aData = $this->getGoodData();

        $this->_form->setValue($aData);

        $this->setSeoDataInGalleryField(
            $oGoodsRow->getRowId() . ':' . $oGoodsRow->getExtCardName(),
            SeoGood::className()
        );

        $this->setSeoBlock($oGoodsRow->getRowId());
    }

    /**
     * Создание формы по моделям полей.
     * @param array $aAttr
     * @param array $aFields
     * @param bool $bDisableUniq
     * @return array
     * @throws \Exception
     */
    protected function initForm(array $aAttr = ['active'], array $aFields = ['id'], $bDisableUniq = false)
    {
        $this->_form = new FormBuilder();

        /** @var \skewer\components\catalog\GoodsRow $oGoodsRow */
        $oGoodsRow = $this->model->getGoodsRow();

        // Сущность расширенной карточки
        $sExtCardName = $oGoodsRow->getExtCardName();
        $oExtEntity = catalog\model\EntityTable::getByName($sExtCardName);
        if (!$oExtEntity) {
            throw new \Exception("Не найдена сущность с именем '{$sExtCardName}'");
        }
        /** Наборы групп и типов групп */
        $aGroupsTypes = [];
        $aGroupList = catalog\Card::getGroupList($aGroupsTypes);

        /* Построить поля формы */

        // Добавить в самый верх специальные поля, типа настройки раздела
        if ($oGoodsRow->isMainRow() and !$bDisableUniq) {
            $this->addSpecialFields();
        }

        /** Сортировать группы полей
         * вместе для каждой расширенной карточки, так как сортировка полей по группам происходит в методе.
         * \skewer\components\catalog\model\EntityRow::updCache() */
        $aFieldsByGroups = $aGroupList;
        foreach ($oGoodsRow->getFields() as $oFtField) {
            $iGroup = (int) $oFtField->getParameter('__group_id');
            if (!isset($aFieldsByGroups[$iGroup])) {
                $iGroup = 0;
            }
            if (is_array($aFieldsByGroups[$iGroup])) {
                $aFieldsByGroups[$iGroup][] = $oFtField;
            } else {
                $aFieldsByGroups[$iGroup] = [$oFtField];
            }
        }
        $fields = [];
        // Добавить поля из карточки
        foreach ($aFieldsByGroups as $iGroup => &$mFtFields) {
            if (is_array($mFtFields)) {
                foreach ($mFtFields as $oFtField) {
                    /** @var ft\model\Field $oFtField */
                    $sGroupTitle = $aGroupList[$iGroup] ?? '';

                    /** Исключить поле из вывода, если установлены определённые атрибуты */
                    $bDisabled = false;
                    $bAddFlag = true;
                    foreach ($aAttr as $sAttrName) {
                        $bAddFlag = ($bAddFlag and (bool)$oFtField->getAttr($sAttrName));
                    }
                    if ($bAddFlag and $bDisableUniq and !in_array($oFtField->getName(), $aFields) and !$oFtField->getAttr('is_uniq')) {
                        $bDisabled = true;
                    }

                    $aAddParams = [
                        'groupTitle' => \Yii::tSingleString($sGroupTitle),
                        'groupType' => $aGroupsTypes[$iGroup] ?? FormBuilder::GROUP_TYPE_DEFAULT,
                        'disabled' => $bDisabled,
                    ];
                    //Если переданное поле является активной яндекс картой
                    if (ApiCatalogMaps::isActiveFieldYandexCard($oFtField)) {
                        foreach ((new YandexSettingsMap())->getAttributes() as $name => $value) {
                            $ftFieldForMap = clone($oFtField);
                            $ftFieldForMap->setEditor(ft\Editor::STRING);
                            $ftFieldForMap->setName("{$oFtField->getName()}_{$name}");
                            $ftFieldForMap->setTitle(\Yii::t('Maps', $name));
                            $fields[$ftFieldForMap->getName()] = $ftFieldForMap;
                        }
                        YandexSettingsMap::setFormView(
                            $this->_form,
                            "{$oFtField->getName()}_",
                            $aAddParams
                        );
                    }
                    $this->makeFormFieldByFtModel($oFtField, $aAddParams);
                    $fields[$oFtField->getName()] = $oFtField;
                }
            }
        }

        // Вывод ошибок для полей
        foreach ($oGoodsRow->getErrorList() as $sFieldName => $sErrorText) {
            if ($oField = $this->_form->getField($sFieldName)) {
                $oField->setError($sErrorText);
            }
        }

        return $fields;
    }

    /**
     * Добавление поля на форму.
     *
     * @param ft\model\Field $oFtField Объект добавляемого поля
     * @param array $aParams Дополнительные параметры поля
     */
    private function makeFormFieldByFtModel(ft\model\Field $oFtField, array $aParams = [])
    {
        // Обработать языковую метку в заголовке поля, если есть разделитель
        $sFieldTitle = \Yii::tSingleString($oFtField->getTitle());

        // Обработка галерейного поля
        if ($oFtField->getEditorName() == ft\Editor::GALLERY) {
            // Получить id профиля галереи для каталога по умолчанию или выбранный через Сущность профиль в редакторе карточки
            try {
                $iDefProfileId = (int) $oFtField->getOption('link_id') ?: Profile::getDefaultId(Profile::TYPE_CATALOG);
            } catch (\Exception $e) {
                // Если не найден каталожный профиль галереи, то вывести предупреждение
                $this->_module->addError($e->getMessage());
                $iDefProfileId = 0;
            }
            $this->_form->fieldGallery($oFtField->getName(), $sFieldTitle, $iDefProfileId, $aParams);
        }
        // Если у поля есть связь и это поле справочное, то получить список возможных значений для выпадающих списков
        elseif (($oRelation = $oFtField->getFirstRelation()) and
            (in_array(
                $oFtField->getEditorName(),
                [ft\Editor::SELECT,
                 ft\Editor::MULTISELECT,
                 ft\Editor::COLLECTION,
                 ft\Editor::MULTICOLLECTION,
                 ft\Editor::SELECTIMAGE,
                 ft\Editor::MULTISELECTIMAGE,
                    ]
            ))) {
            $oModel = ft\Cache::get($oRelation->getEntityName());

            $sValName = $sTitleField = $oRelation->getExternalFieldName();
            if ($oModel->hasField('title')) {
                $sTitleField = 'title';
            } elseif ($oModel->hasField('name')) {
                $sTitleField = 'name';
            }

            // Получение значений справочника
            if (in_array($oFtField->getEditorName(), [ft\Editor::SELECT,
                                                      ft\Editor::MULTISELECT,
                                                      ft\Editor::SELECTIMAGE,
                                                      ft\Editor::MULTISELECTIMAGE, ])) {
                $aItems = catalog\Dict::getValues($oRelation->getEntityName());
            } else {
                // Или значений коллекицй
                $aItems = ft\Cache::getMagicTable($oRelation->getEntityName())->find()->getAll();
            }

            $aList = [];
            foreach ($aItems as $oItem) {
                $aList[$oItem->{$sValName}] = $oItem->{$sTitleField};
            }

            if (in_array($oFtField->getEditorName(), [ft\Editor::MULTISELECT,
                                                      ft\Editor::MULTICOLLECTION,
                                                      ft\Editor::MULTISELECTIMAGE, ])) {
                $this->_form->fieldMultiSelect($oFtField->getName(), $sFieldTitle, $aList, '', $aParams);
            } else {
                $this->_form->fieldSelect($oFtField->getName(), $sFieldTitle, $aList, $aParams);
            }
            //спец поле для платежной системы признак предмета расчета
        } elseif ($oFtField->getEditorName() == ft\Editor::PAYMENTOBJECT) {
            $this->_form->fieldSelect($oFtField->getName(), $sFieldTitle, \skewer\build\Tool\Payments\Api::getTitlePaymentObject(), $aParams);
        // Остальные поля
        } else {
            $this->_form->field($oFtField->getName(), $sFieldTitle, $oFtField->getEditorName(), $aParams);
        }
    }

    /** Добавить поля настройки разделов каталожной позиции */
    private function addSpecialFields()
    {
        $iItemId = $this->model->getGoodsRow()->getRowId();

        $aCatSection = catalog\Section::getList();
        $aGoodsSection = $this->model->getGoodsRow()->getViewSection();

        // Текущий раздел для нового товара
        if (!$iItemId and empty($aGoodsSection)) {
            $aGoodsSection = [$this->_module->getCurrentSection()];
        }

        $aGoodsSectionWithTitle = Tree::getSectionsTitle($aGoodsSection, false, true);
        $iMainSectionId = $this->model->getGoodsRow()->getMainSection();

        // если главная секция не задана, но есть варианты - подставить первый
        if (!$iMainSectionId and $aGoodsSectionWithTitle) {
            $aSections = array_keys($aGoodsSectionWithTitle);
            $iMainSectionId = array_shift($aSections);
        }

        // Основной раздел
        $this->_form->fieldSelect(self::mainLinkField, \Yii::t('catalog', 'main_section'), $aGoodsSectionWithTitle, [
            'groupTitle' => \Yii::t('catalog', 'settings'),
            'value' => $iMainSectionId,
        ], false);

        // Категории
        $this->_form->fieldMultiSelect(self::categoryField, \Yii::t('catalog', 'category'), $aCatSection, $aGoodsSection, [
            'groupTitle' => \Yii::t('catalog', 'settings'),
            'onUpdateAction' => 'loadSection',
        ]);
    }

    // Сопутствующие
    private function btnShowRelatedItems()
    {
        if (SysVar::get('catalog.goods_related')) {
            $this->_form->buttonCustomExt(
                ext\docked\Api::create(\Yii::t('catalog', 'relatedItems'))
                    ->setIconCls(ext\docked\Api::iconEdit)
                    ->setState('RelatedItems')
                    ->setAction('RelatedItems')
                    ->unsetDirtyChecker()
            );
        }
    }

    // В комплекте
    private function btnShowIncludedItems()
    {
        if (SysVar::get('catalog.goods_include')) {
            $this->_form->buttonCustomExt(
                ext\docked\Api::create(\Yii::t('catalog', 'includedItems'))
                    ->setIconCls(ext\docked\Api::iconEdit)
                    ->setState('IncludedItems')
                    ->setAction('IncludedItems')
                    ->unsetDirtyChecker()
            );
        }
    }

    // Модификации товаров
    private function btnShowModificationsItems()
    {
        if (SysVar::get('catalog.goods_modifications')) {
            $this->_form->buttonCustomExt(
                ext\docked\Api::create(\Yii::t('catalog', 'modificationsItems'))
                    ->setIconCls(ext\docked\Api::iconEdit)
                    ->setState('ModificationsItems')
                    ->setAction('ModificationsItems')
                    ->unsetDirtyChecker()
            );
        }
    }

    protected function filterByActive($oModelFieldList, $bGoodsExists)
    {
        /*Фильтр по "только активные"*/
        if (self::$bOnlyActive) {
            $aFormFields = $this->_form->getForm()->getFields();

            $aOutFormFields = [];

            foreach ($aFormFields as $oFormField) {
                if (in_array($oFormField->getName(), ['id', self::mainLinkField, self::categoryField])) {
                    $bAdd = true;
                } else {
                    if (!isset($oModelFieldList[$oFormField->getName()])) {
                        continue;
                    }

                    $oModelField = $oModelFieldList[$oFormField->getName()];

                    $bAdd = $oModelField->getAttr('active');

                    // Если редактируется существующая каталожная позиция
                    if ($bGoodsExists) {
                        // Флаг для запрета редактирования поля
                        $bDisabled = $oModelField->getAttr('not_edit_field');
                        // Запрет редактирования поля
                        if ($bDisabled) {
                            $oFormField->setDescVal('disabled', true);
                        }
                    }
                }

                if ($bAdd) {
                    $aOutFormFields[] = $oFormField;
                }
            }

            $this->_form->getForm()->setFields($aOutFormFields);
        }
    }

    protected function filterByVisible()
    {
        $aHiddenFields = \skewer\build\Catalog\CardEditor\Api::getHiddenFields();
        $aFormFields = $this->_form->getForm()->getFields();
        $aOutFormFields = [];

        foreach ($aFormFields as $oFormField) {
            // Исключить скрытые поля
            if (array_search($oFormField->getName(), $aHiddenFields) !== false) {
                continue;
            }
            $aOutFormFields[] = $oFormField;
        }

        $this->_form->getForm()->setFields($aOutFormFields);
    }

    /**
     * Установка seo данных в поле галерея.
     *
     * @param string $sEntityId - id сущности
     * @param string $sSeoClass - класс seo компонента
     */
    protected function setSeoDataInGalleryField($sEntityId, $sSeoClass)
    {
        $this->_form->getField('gallery')->setDescVal('iEntityId', $sEntityId);
        $this->_form->getField('gallery')->setDescVal('seoClass', $sSeoClass);
        $this->_form->getField('gallery')->setDescVal('sectionId', $this->_module->getCurrentSection());
    }

    /**
     * Добавляет seo-блок во вьюху.
     *
     * @param int|string $iGoodId - ид товара
     */
    protected function setSeoBlock($iGoodId)
    {
        /** @var bool $bIsCommonList Это общий список товаров(Фильтр = Раздел:Все)? */
        $bIsCommonList = !(bool) $this->_module->getCurrentSection();

        if (!$bIsCommonList) {
            $bIsNewGood = !(bool) $iGoodId;

            $aGood = $bIsNewGood
                ? SeoGood::getBlankGood($this->_module->getCardName())
                : catalog\GoodsSelector::get($iGoodId);

            $oSeo = new SeoGood();
            $oSeo->setDataEntity($aGood);
            $oSeo->setSectionId($this->_module->getCurrentSection());
            $oSeo->setExtraAlias($this->_module->getCardName());

            Api::appendExtForm($this->_form, $oSeo, $this->_module->getCurrentSection(), []);
        } else {
            $this->_form->fieldWithValue('warning_text', \Yii::t('SEO', 'warning'), 'show', \Yii::t('SEO', 'warning_text'), ['groupTitle' => \Yii::t('SEO', 'group_title'), 'groupType' => FormBuilder::GROUP_TYPE_COLLAPSIBLE]);
        }
    }

    /**
     * Получить подготовленные для вывода данные товара.
     *
     * @return array
     */
    protected function getGoodData()
    {
        $oGoodsRow = $this->model->getGoodsRow();

        // Обработка полей для вывода по типам
        $aFields = $oGoodsRow->getFields();
        $aData = $oGoodsRow->getData();

        if (isset($aData['price'])) {
            $aData['price'] = Twig::priceFormat($aData['price'], 0);
        }

        foreach ($aFields as $oFtField) {
            if (isset($aData[$oFtField->getName()]) && $oFtField->getDatatype() == 'int') {
                $aData[$oFtField->getName()] = (int) $aData[$oFtField->getName()];
            }
            if (isset($aData[$oFtField->getName()]) && $oFtField->getDatatype() == 'decimal(12,2)') {
                $aData[$oFtField->getName()] = (float) $aData[$oFtField->getName()];
            }
        }

        $aExtFields = $this->_form->getForm()->getFields();

        foreach ($aExtFields as $oExtField) {
            if ($oExtField->getView() == ft\Editor::MAP_SINGLE_MARKER) {
                if (ApiCatalogMaps::getActiveProvider() == ApiCatalogMaps::providerYandexMap) {
                    $geoObjectId = $aData[$oExtField->getName()] ?: null;
                    $yandexMap = new YandexSettingsMap($geoObjectId);
                    foreach ($yandexMap->getAttributes() as $name => $value) {
                        $aData["{$oExtField->getName()}_{$name}"] = $value;
                    }
                    /** удаление поля с яндекс картой из вывода в списке */
                    $this->_form->removeField($oExtField->getName());
                } else {
                    // Подмена значения в поле
                    $aData[$oExtField->getName()] = CatalogMaps\Api::getAddressGeoObjectFormatted($aData[$oExtField->getName()]);
                }
            }
        }

        return $aData;
    }
}
