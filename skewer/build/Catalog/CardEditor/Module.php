<?php

namespace skewer\build\Catalog\CardEditor;

use skewer\base\ft\Editor;
use skewer\base\section\models\ParamsAr;
use skewer\base\SysVar;
use skewer\build\Catalog\Goods\Search;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Catalog\LeftList\ModulePrototype;
use skewer\components\auth\CurrentAdmin;
use skewer\components\catalog;
use skewer\components\filters;
use skewer\components\import\Task;
use skewer\components\seo\Template;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Модуль редактора карточек товаров
 * Class Module.
 */
class Module extends ModulePrototype
{
    // число элементов на страницу
    public $iOnPage = 20;

    // текущий номер страницы ( с 0, а приходит с 1 )
    public $iPage = 0;

    /** Список системных каталожных полей базовой карточки */
    private static $SYSTEM_FIELDS = [
        'alias', 'title', 'article', 'announce', 'obj_description', 'price', 'measure', 'gallery', 'old_price',  // Характеристики товара
        'active', 'buy', 'fastbuy', 'on_main', 'hit', 'new', 'discount', 'countbuy',     // Элементы управления
        'tag_title', 'keywords', 'description', 'sitemap_priority', 'sitemap_frequency', // SEO
    ];

    /** Параметры полей, запрещённые для редактирования у системных полей базовой карточки */
    private static $PROTECTED_PARAMS = ['name', /*'editor',*/ 'link_id', 'validator'];

    /** Параметры полей, запрещённые для редактирования у системных полей базовой карточки для системного администратора сайта */
    private static $PROTECTED_PARAMS_SYS = [];

    /**
     * Getter для self::$SYSTEM_FIELDS.
     *
     * @return array
     */
    protected static function getSystemFields()
    {
        $aFields = self::$SYSTEM_FIELDS;

        $aFields[] = Task::$sHashFieldName;
        $aFields[] = Task::$sUpdatedFieldName;

        return $aFields;
    }

    /**
     * @throws UserException
     *
     * @return mixed|string
     */
    private function getCardId()
    {
        $card = $this->getInnerData('card', 0);
        if (!$card) {
            $card = $this->getInDataVal('id');
        }
        if (!$card) {
            throw new UserException('Card not found');
        }
        $this->setInnerData('card', $card);

        return $card;
    }

    public function actionInit()
    {
        $this->actionCardList();
    }

    /**
     * Список карточек товара.
     */
    public function actionCardList()
    {
        $this->setInnerData('card', 0);

        // установка заголовка
        $this->setPanelName(\Yii::t('card', 'title_card_list'));

        $this->render(new view\CardList([
            'oGoodsCards' => catalog\Card::getGoodsCards(true),
        ]));
    }

    /**
     * Форма редактирования основных параметров карточки товара.
     */
    public function actionCardEdit()
    {
        $iCardId = $this->getInnerData('card');

        if ($iCardId) {
            $oCard = catalog\Card::get($iCardId);
            $this->setPanelName(\Yii::t('card', 'title_card_editor', $oCard->title));
        } else {
            $oCard = catalog\Card::get();
            $this->setPanelName(\Yii::t('card', 'title_new_card'));
        }

        $this->render(new view\CardEdit([
            'iCardId' => $iCardId,
            'bIsBasic' => $oCard->isBasic(),
            'aBasicCardList' => Api::getBasicCardList($iCardId),
            'oCard' => $oCard,
            'sPaymentObject' => SysVar::get(catalog\Card::PREFIX_PAYMENT_OBJECT_NAME . $oCard->name),
            'aPaymentObject' => Api::getTitlePaymentObject(),
        ]));
    }

    /**
     * Сохранение основных параметров карточки товара.
     *
     * @throws UserException
     * @throws \Exception
     */
    public function actionCardSave()
    {
        $aData = $this->getInData();
        $id = ArrayHelper::getValue($aData, 'id', null);
        $type = ArrayHelper::getValue($aData, 'type', '');
        $parent = ArrayHelper::getValue($aData, 'parent', '');
        $paymentObject = ArrayHelper::getValue($aData, 'payment_object', '');

        if (!($title = ArrayHelper::getValue($aData, 'title', ''))) {
            throw new UserException(\Yii::t('card', 'error_no_card_name'));
        }
        if ($type != catalog\Card::TypeBasic && !$parent) {
            throw new UserException(\Yii::t('card', 'error_no_base_card'));
        }
        // всегда только расширенная карточка
        if ($type != catalog\Card::TypeBasic) {
            $aData['type'] = catalog\Card::TypeExtended;
        }

        $aData['module'] = 'Catalog';

        $oCard = catalog\Card::get($id);
        $oCard->setData($aData);
        $oCard->save();
        $oCard->updCache();

        if ($type == catalog\Card::TypeBasic && $paymentObject == '') {
            throw new UserException(\Yii::t('card', 'error_no_empty_payment_object'));
        }
        SysVar::set(catalog\Card::PREFIX_PAYMENT_OBJECT_NAME . $oCard->name, $paymentObject);

        $this->setInnerData('card', $oCard->id);

        Search::rebuildSearchByCardName($oCard->name, !ArrayHelper::getValue($aData, 'hide_detail'));

        $this->actionFieldList();
    }

    /**
     * Удаление карточки товара.
     *
     * @throws UserException
     */
    public function actionCardRemove()
    {
        $id = $this->getCardId();
        $oCard = catalog\Card::get($this->getCardId());

        if (!$id || !$oCard) {
            throw new UserException(\Yii::t('card', 'error_card_not_found'));
        }
        /*Проверим, есть ли разделы связанные с этой карточкой*/
        $bSectionsExists = (bool) catalog\model\SectionTable::find()
            ->where(['goods_ext_card' => $id])
            ->getCount();

        $bSectionsParamExists = (bool) ParamsAr::find()->where(['value' => $oCard->name,
            'group' => 'content',
            'name' => 'defCard', ])->count();

        if ($bSectionsExists || $bSectionsParamExists) {
            throw new UserException(\Yii::t('card', 'error_card_remove'));
        }
        $bGoodsExists = (bool) catalog\model\GoodsTable::find()
            ->where(['ext_card_id' => $id])
            ->getCount();

        if ($bGoodsExists) {
            throw new UserException(\Yii::t('card', 'error_goods_card_remove'));
        }
        if (count(catalog\Card::getGoodsCards(false)) < 2) {
            throw new UserException(\Yii::t('card', 'error_no_del_card'));
        }
        SysVar::del(catalog\Card::PREFIX_PAYMENT_OBJECT_NAME . $oCard->name);

        // Удаление seo шаблона
        if ($oSeoTpl = Template::getByAliases(SeoGood::getAlias(), $oCard->name)) {
            $oSeoTpl->delete();
        }

        //костыль на очистку данных карточки
        foreach ($oCard->getFields() as $oField) {
            /*Запускаем валидатор удаления поля по типу поля*/
            if (class_exists('skewer\\components\\catalog\\field\\' . ucfirst($oField->editor))
                && method_exists('skewer\\components\\catalog\\field\\' . ucfirst($oField->editor), 'validateFieldDelete')) {
                call_user_func_array('skewer\\components\\catalog\\field\\' . ucfirst($oField->editor) . '::validateFieldDelete', [$oField]);
            }

            // Запретить удаление системных полей базовой карточки
            if (($oCard->name == catalog\Card::DEF_BASE_CARD) and in_array($oField->name, self::getSystemFields())) {
                throw new UserException(\Yii::t('dict', 'error_field_cant_removed'));
            }
            $oField->delete();
        }
        catalog\Card::build($oCard->id);

        $oCard->delete();

        $this->actionCardList();
    }

    /**
     * Список полей для карточки товара.
     *
     * @throws UserException
     */
    public function actionFieldList()
    {
        $oCard = catalog\Card::get($this->getCardId());

        $sHeadText = \Yii::t('card', 'head_card_name', $oCard->title);
        $this->setPanelName(\Yii::t('card', 'title_field_list', $oCard->title));

        /* Устанавливаем значения */
        $aFields = $oCard->getFields();
        $aFieldsOut = [];
        $aHiddenFields = Api::getHiddenFields();

        // Обработать языковую метку в заголовке поля, если есть разделитель
        // Исключить скрытые поля
        foreach ($aFields as &$oField) {
            if (array_search($oField->name, $aHiddenFields) === false) {
                $oField->title = \Yii::tSingleString($oField->title);
                $aFieldsOut[] = $oField;
            }
        }

        $aFields = $aFieldsOut;

        $this->render(new view\FieldList([
            'sHeadText' => $sHeadText,
            'aFields' => $aFields,
            'bIsExtendedCard' => $oCard->isExtended(),
        ]));
    }

    /**
     * Обработчик события сортировки полей карточки товара.
     */
    protected function actionSortFields()
    {
        $aData = $this->get('data');
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        if (catalog\model\FieldTable::sort($aData, $aDropData, $sPosition)) {
            catalog\Card::build($aData['entity']);
        }
    }

    /**
     * Форма редактирование поля карточки товара.
     *
     * @return int
     */
    public function actionFieldEdit()
    {
        $iFieldId = $this->getInDataValInt('id');
        $iCardId = $this->getInnerData('card', 0);

        $oCard = catalog\Card::get($iCardId);
        $sHeadText = \Yii::t('card', 'head_card_name', $oCard->title);

        if ($iFieldId) {
            $oField = catalog\Card::getField($iFieldId);
            $oField->validator = $oField->getValidatorList();
            $this->setPanelName(\Yii::t('card', 'title_edit_field'));
        } else {
            $oField = catalog\Card::getField();
            $this->setPanelName(\Yii::t('card', 'title_new_field'));
        }

        $bIsSystemFieldInBaseCard = ($oCard->name == catalog\Card::DEF_BASE_CARD) and in_array($oField->name, self::$SYSTEM_FIELDS);
        $aProtectedParams = null;
        if ($bIsSystemFieldInBaseCard) {
            $aProtectedParams = CurrentAdmin::isSystemMode() ? self::$PROTECTED_PARAMS_SYS : self::$PROTECTED_PARAMS;
        }

        $this->render(new view\FieldEdit([
            'sHeadText' => $sHeadText,
            'iFieldId' => $iFieldId,
            'aGroupList' => catalog\Card::getGroupList(),
            'aSimpleTypeList' => Api::getSimpleTypeList(),
            'oField' => $oField,
            'aEntityList' => Api::getEntityList($oField),
            'aSimpleWidgetList' => Api::getSimpleWidgetList($oField),
            'aAttrList' => $oField->getAttr(CurrentAdmin::isSystemMode()),
            'aListWithTitles' => catalog\Validator::getListWithTitles(),
            'bIsSystemFieldInBaseCard' => $bIsSystemFieldInBaseCard,
            'aProtectedParams' => $aProtectedParams,
        ]));

        return psComplete;
    }

    /**
     * Обработчик изменения значения поля editor ("Тип отображения").
     */
    public function actionUpdFields()
    {
        $aFormData = $this->get('formData', []);
        $sEditor = $aFormData['editor'] ?? '';
        $iTypeId = $aFormData['link_id'] ?? '';
        $sWidget = $aFormData['widget'] ?? '';

        $id = $aFormData['id'] ?? null;

        $oField = catalog\Card::getField($id);
        $oField->editor = $sEditor;
        $oField->link_id = $iTypeId;

        $aLinkList = Api::getEntityList($oField);
        $aWidgetList = Api::getSimpleWidgetList($oField);

        // Выбрать профиль для галереи по умолчанию
        if (!$iTypeId) {
            $sNameFieldClass = catalog\Api::getClassField($sEditor);
            if (method_exists($sNameFieldClass, 'getDefaultGallery')) {
                $aWidgetList = $sNameFieldClass::getDefaultGallery($iTypeId);
            }
        }

        if ($id) {
            $this->addWarning(\Yii::t('card', 'field_change_warning_title'), \Yii::t('card', 'field_change_warning'));
        }

        $view = new view\UpdFields([
            'aLinkList' => $aLinkList,
            'bFieldIsNotLinked' => !$oField->isLinked(),
            'aWidgetList' => $aWidgetList,
            'iLinkId' => isset($aLinkList[$iTypeId]) ? $iTypeId : '',
            'sWidget' => isset($aWidgetList[$sWidget]) ? $sWidget : '',
        ]);
        $view->build();
        $this->setInterfaceUpd($view->getInterface());
    }

    /**
     * Состояние сохранение поля.
     *
     * @throws UserException
     * @throws \Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionFieldSave()
    {
        $card = $this->getInnerData('card', 0);
        $data = $this->getInData();
        $id = ArrayHelper::getValue($data, 'id', null);
        $title = ArrayHelper::getValue($data, 'title', '');
        $validators = ArrayHelper::getValue($data, 'validator', '');

        $oField = catalog\Card::getField($id);
        $editor = ArrayHelper::getValue($data, 'editor', $oField->editor);
        $oCard = catalog\Card::get($card);
        $bIsSysField = (($oCard->name == catalog\Card::DEF_BASE_CARD) and in_array($oField->name, self::getSystemFields()));

        if (!$title) {
            throw new UserException(\Yii::t('card', 'error_no_field_name'));
        }
        if (!$card) {
            throw new UserException(\Yii::t('card', 'error_card_not_found'));
        }
        // В системных полях запрещено менять тип редактора, поэтому не нужно проверять этот параметр, поскольку он приходит пустой
        if (!$editor and !$bIsSysField) {
            throw new UserException(\Yii::t('card', 'error_no_editor_for_field'));
        }
        $sNameFieldClass = catalog\Api::getClassField($editor);
        if ($sNameFieldClass) {
            /** @var catalog\field\Prototype $oFieldClass */
            $oFieldClass = new $sNameFieldClass();

            $iLinkId = ArrayHelper::getValue($data, 'link_id', false);
            if (!$iLinkId && $oField->id != 0) {
                $iLinkId = $oField->link_id;
            }

            if ($oFieldClass->isSpecialEdit and !$iLinkId) {
                throw new UserException(\Yii::t('card', 'error_no_link_id_for_editor'));
            }
        }

        if ($editor == Editor::GALLERY && ArrayHelper::getValue($data, 'attr_' . catalog\Attr::SHOW_IN_SEARCH)) {
            throw new UserException(\Yii::t('card', 'field_can_not_used_in_search', [Api::getSimpleTypeList()[$editor]]));
        }

        if (ArrayHelper::getValue($data, 'attr_' . catalog\Attr::SHOW_IN_FILTER)) {
            $sClassNameCatalogField = catalog\Api::getClassField($editor, true);
            $oCatalogField = new $sClassNameCatalogField();
            $oWidget = filters\widgets\Prototype::getInstanceWidget($oCatalogField);
            if (!$oWidget) {
                throw new UserException(\Yii::t('card', 'field_can_not_used_in_filter', [Api::getSimpleTypeList()[$editor]]));
            }
        }

        $oField::$aValidationMode = [
            'type' => 'card_field',
            'card_id' => $card,
        ];

        $oField->setData($data);
        $oField->entity = $card;
        $oField->save();

        // сохранение валидаторов, если это поле не системное
        if (!$bIsSysField or CurrentAdmin::isSystemMode()) {
            $oField->setValidator($validators);
        }

        // сохранение атрибутов для поля
        foreach ($data as $sKey => $sValue) {
            if (!isset($oField->{$sKey}) && mb_strpos($sKey, 'attr_') === 0) {
                $oField->setAttr(mb_substr($sKey, 5), $sValue);
            }
        }

        // rebuild card
        catalog\Card::build($oField->entity);

        $this->actionFieldList();
    }

    /**
     * Удаление поля.
     *
     * @throws \skewer\base\ft\Exception
     * @throws UserException
     */
    public function actionFieldRemove()
    {
        $data = $this->getInData();

        $iCardId = $this->getInnerData('card', 0);
        $oCard = catalog\Card::get($iCardId);
        if (!$oCard) {
            throw new UserException(\Yii::t('card', 'error_card_not_found'));
        }
        $id = ArrayHelper::getValue($data, 'id', null);

        if (!$id) {
            throw new UserException(\Yii::t('card', 'error_field_not_found'));
        }
        $oField = catalog\Card::getField($id);

        /*Запускаем валидатор удаления поля по типу поля*/
        if (class_exists('skewer\\components\\catalog\\field\\' . ucfirst($oField->editor))
            && method_exists('skewer\\components\\catalog\\field\\' . ucfirst($oField->editor), 'validateFieldDelete')) {
            call_user_func_array('skewer\\components\\catalog\\field\\' . ucfirst($oField->editor) . '::validateFieldDelete', [$oField]);
        }

        // Запретить удаление системных полей базовой карточки
        if (($oCard->name == catalog\Card::DEF_BASE_CARD) and in_array($oField->name, self::getSystemFields())) {
            throw new UserException(\Yii::t('dict', 'error_field_cant_removed'));
        }
        $oField->delete();

        // rebuild card
        catalog\Card::build($oField->entity);

        $this->actionFieldList();
    }

    /**
     * Список групп для карточки товара.
     */
    public function actionGroupList()
    {
        $this->setPanelName(\Yii::t('card', 'title_group_list'));
        $this->render(new view\GroupList([
            'aCardGroup' => catalog\Card::getGroups(),
        ]));
    }

    /** Обработчик события сортировки групп карточек */
    protected function actionSortGroups()
    {
        $aData = $this->get('data');
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        if (catalog\model\FieldGroupTable::sortGroups($aData['id'], $aDropData['id'], $sPosition)) {
            // Обновить кэши полей карточек, группы которых были отсортированы
            $aGroupFields = catalog\model\FieldTable::find()
                ->where('group', $aData['id'])
                ->orWhere('group', $aDropData['id'])
                ->asArray()
                ->getAll();

            $aUpdatingCards = [];

            if ($aGroupFields) {
                foreach ($aGroupFields as $aCardField) {
                    $aUpdatingCards[$aCardField['entity']] = 1;
                }
            }

            foreach (array_keys($aUpdatingCards) as $sCardId) {
                catalog\Card::build($sCardId);
            }
        }

        $this->actionGroupList();
    }

    /**
     * Форма редактирования групп
     *
     * @return int
     */
    public function actionGroupEdit()
    {
        $iGroupId = $this->getInDataVal('id');

        if ($iGroupId) {
            $this->setPanelName(\Yii::t('card', 'title_edit_group'));
            $oGroup = catalog\Card::getGroup($iGroupId);
        } else {
            $this->setPanelName(\Yii::t('card', 'title_new_group'));
            $oGroup = catalog\Card::getGroup();
        }

        $this->render(new view\GroupEdit([
            'iGroupId' => $iGroupId,
            'oGroup' => $oGroup,
        ]));

        return psComplete;
    }

    /**
     * Сохранение группы.
     *
     * @throws UserException
     */
    public function actionGroupSave()
    {
        $id = $this->getInDataVal('id');

        if (!$this->getInDataVal('title')) {
            throw new UserException(\Yii::t('card', 'error_no_group_name'));
        }
        $oGroup = catalog\Card::getGroup($id);
        $oGroup->setData($this->get('data'));
        $oGroup->save();

        $this->actionGroupList();
    }

    /**
     * Удаление группы.
     */
    public function actionGroupRemove()
    {
        $iGroupId = $this->getInDataVal('id');

        $oGroup = catalog\Card::getGroup($iGroupId);

        if ($iGroupId && $oGroup) {
            $oGroup->delete();
        }

        $this->actionGroupList();
    }
}
