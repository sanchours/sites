<?php

namespace skewer\build\Tool\Dictionary;

use skewer\base\ft;
use skewer\base\ui;
use skewer\build\Catalog\CardEditor;
use skewer\build\Cms\FileBrowser;
use skewer\build\Tool\Dictionary\view\AddNewDictionary;
use skewer\build\Tool\Dictionary\view\FieldEdit;
use skewer\build\Tool\Dictionary\view\FieldList;
use skewer\build\Tool\Dictionary\view\Index;
use skewer\build\Tool\Dictionary\view\ItemEdit;
use skewer\build\Tool\Dictionary\view\UpdFieldLinkId;
use skewer\build\Tool\Dictionary\view\View;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\components\auth\CurrentAdmin;
use skewer\components\catalog;
use skewer\components\gallery\Profile;
use yii\base\UserException;

/**
 * Модуль для справочников
 * Class Module.
 */
class Module extends ModulePrototype
{
    // число элементов на страницу
    public $iOnPage = 20;

    // текущий номер страницы ( с 0, а приходит с 1 )
    public $iPage = 0;

    protected function preExecute()
    {
        $this->iOnPage = \Yii::$app->getParam(['dict_on_page']);
    }

    protected function getCard()
    {
        $card = $this->getInnerData('card', 0);

        if (!$card) {
            $card = $this->getInDataVal('name');
        }

        $this->setInnerData('card', $card);

        return $card;
    }

    /**
     * Иницализация.
     */
    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список справочников.
     */
    protected function actionList()
    {
        $this->setInnerData('card', 0);

        // установка заголовка
        $this->setPanelName(\Yii::t('dict', 'dict_list_for_cat'));

        $aDict = catalog\Dict::getDictionaries($this->getLayerName());
        $aDictBanDel = catalog\Dict::getBanDelDict();
        $aDictBanEdit = catalog\Dict::getBanEditDict();

        foreach ($aDict as &$oDict) {
            $iBanDelDict = ($aDictBanDel && in_array($oDict->name, $aDictBanDel)) ? 1 : 0;
            $iBanEditDict = ($aDictBanEdit && in_array($oDict->name, $aDictBanEdit)) ? 1 : 0;
            $oDict->setVal('banDelDict', $iBanDelDict, false);
            $oDict->setVal('banEditDict', $iBanEditDict, false);
        }

        $this->render(new Index([
            'isSys' => CurrentAdmin::isSystemMode(),
            'aDictionaries' => $aDict,
            'sLayer' => $this->getLayerName(),
        ]));
    }

    protected function actionChangeDictBanDel()
    {
        $nameDict = $this->getInDataVal('name', 0);
        $sBanDelDict = $this->getInDataVal('banDelDict');

        if ($sBanDelDict) {
            catalog\Dict::setBanDelDict($nameDict);
        } else {
            catalog\Dict::enableDelDict($nameDict);
        }

        $this->actionList();
    }

    protected function actionChangeDictEditDel()
    {
        $nameDict = $this->getInDataVal('name', 0);
        $sBanDelDict = $this->getInDataVal('banEditDict');

        if ($sBanDelDict) {
            catalog\Dict::setBanEditDict($nameDict);
        } else {
            catalog\Dict::enableEditDict($nameDict);
        }

        $this->actionList();
    }

    /**
     *  Действие. Изменение имени справочника.
     *
     * @throws  UserException
     */
    protected function actionChangeDictName()
    {
        $iDictid = (int) $this->getInDataVal('id', 0);
        $sDictTitle = $this->getInDataVal('title', '');

        if (!$sDictTitle) {
            throw new UserException(\Yii::t('dict', 'error_noname_seted'));
        }
        if (catalog\model\EntityTable::find()->where('title', $sDictTitle)->getOne()) {
            throw new UserException(\Yii::t('dict', 'error_name_bosy'));
        }
        if ($iDictid and $sDictTitle) {
            $oDict = catalog\Card::get($iDictid);

            if ($oDict) {
                $oDict->title = $sDictTitle;
                $oDict->save();

                $this->updateRow($oDict->getData());
            }
        }
    }

    /**
     * Набор значений справочника.
     */
    protected function actionView()
    {
        $this->iPage = $this->getInt('page');
        $iCount = 0;

        // обработка входных данных
        $card = $this->getCard();

        $aItems = catalog\Dict::getDictTable($card, $this->iPage, $this->iOnPage, $iCount);

        // установка заголовка
        $this->setPanelName(\Yii::t('dict', 'dict_panel_name', catalog\Card::getTitle($card)));

        //обработка запрета на удаление
        $aBanDelDict = catalog\Dict::getBanDelDict();
        $aBanEditDict = catalog\Dict::getBanEditDict();
        $bBanEditDict = in_array($card, $aBanEditDict);
        $bBanDelDict = in_array($card, $aBanDelDict);
        $card = catalog\Card::getId($card);
        $oFieldTitle = catalog\Card::getFieldByName($card, 'title');
        $oFieldAlias = catalog\Card::getFieldByName($card, 'alias');

        $this->render(new View([
            'aItems' => $aItems,
            'bBanDelDict' => $bBanDelDict,
            'bBanEditDict' => $bBanEditDict,
            'page' => $this->iPage,
            'onPage' => $this->iOnPage,
            'total' => $iCount,
            'titleDict' => catalog\Card::getTitle($card),
            'titleName' => $oFieldTitle->title,
            'aliasName' => $oFieldAlias->title,
        ]));
    }

    /**
     * Список полей справочника.
     *
     * @throws UserException
     */
    protected function actionFieldList()
    {
        $card = $this->getInnerData('card');

        if (!$card) {
            throw new UserException('Card not found!');
        }
        // генерация объектов для работы
        $oCard = catalog\Card::get($card);

        $sHeadText = \Yii::t('catalog', 'head_dict_name', $oCard->title);
        $this->setPanelName(\Yii::t('catalog', 'title_dict_field_list', $oCard->title));

        // устанавливаем значения, исключая поле для сортировки
        $aFields = $oCard->getFields();
        foreach ($aFields as $iKey => $oField) {
            if (in_array($oField->name, catalog\Dict::$aNotRemoveField)) {
                $oField->prohib_del = 1;
                $aFields[$iKey] = $oField;
            }
            if ($oField->name == catalog\Card::FIELD_SORT) {
                unset($aFields[$iKey]);
            }
        }

        $this->render(new FieldList([
            'aFields' => $aFields,
            'sHeadText' => $sHeadText,
        ]));
    }

    /**
     * Интерфейс создания/редактирования поля справочника.
     *
     * @throws UserException
     */
    protected function actionFieldEdit()
    {
        // входные параметры
        $card = $this->getInnerData('card');
        $iFieldId = $this->getInDataVal('id', false);

        if (!$card) {
            throw new UserException('Card not found!');
        }
        /** @var catalog\model\FieldRow $oItem */
        $oCard = catalog\Card::get($card);

        if ($iFieldId) {
            $oItem = catalog\Card::getField($iFieldId);
            $this->setPanelName(\Yii::t('dict', 'title_edit_field'));
        } else {
            $oItem = catalog\Card::getField();
            $this->setPanelName(\Yii::t('dict', 'title_new_field'));
        }

        // Добавить использующийся профиль галереи или словаря независимости от его активности
        if ($oItem->editor == ft\Editor::GALLERY) {
            $aResult = Profile::getAll(true, true, true);
            if ($aProfileCurrent = Profile::getById($oItem->link_id)) {
                $aResult[$oItem->link_id] = $aProfileCurrent['title'];
            }
            $sTitleLinkId = \Yii::t('gallery', 'profiles_select');
        } elseif ($oItem->editor == ft\Editor::SELECT) {
            $aResult = catalog\Dict::getDictAsArray($this->getLayerName());
            $aResult[$oItem->link_id] = isset($aResult[$oItem->link_id]) ? $aResult[$oItem->link_id] : '';
        }
        $sTitleLinkId = (isset($sTitleLinkId)) ? $sTitleLinkId : \Yii::t('dict', 'field_f_link_id');

        $this->render(new FieldEdit([
            'sCardTitle' => $oCard->title,
            'iFieldId' => $iFieldId,
            'sTitleLinkId' => $sTitleLinkId,
            'aSimpleTypeList' => CardEditor\Api::getSimpleTypeList(false),
            'aResult' => (isset($aResult)) ? $aResult : [],
            'oItem' => $oItem,
        ]));
    }

    /** Обработчик изменения значения поля editor ("Тип отображения") */
    public function actionUpdFieldLinkId()
    {
        $aFormData = $this->get('formData', []);
        $sEditor = $aFormData['editor'] ?? '';
        $iTypeId = $aFormData['link_id'] ?? '';

        $id = $aFormData['id'] ?? null;
        $oField = catalog\Card::getField($id);
        $oField->editor = $sEditor;

        if ($sEditor == ft\Editor::SELECT) {
            $aProfiles = catalog\Dict::getDictAsArray($this->getLayerName());
            $sTitleLinkId = \Yii::t('dict', 'field_f_link_id');
        } elseif ($sEditor == ft\Editor::GALLERY) {
            $aProfiles = Profile::getAll(true, true, true);
            $sTitleLinkId = \Yii::t('gallery', 'profiles_select');
        } else {
            $aProfiles = [];
            $sTitleLinkId = '';
        }

        $view = new UpdFieldLinkId([
            'aProfiles' => $aProfiles,
            'sTitleLinkId' => $sTitleLinkId,
            'bIsNotLinked' => !$oField->isLinked(),
            'iTypeId' => $iTypeId,
        ]);
        $view->build();
        $this->setInterfaceUpd($view->getInterface());
    }

    /**
     * @throws UserException
     * @throws \Exception
     */
    protected function actionFieldSave()
    {
        $card = $this->getInnerData('card');

        if (!$card) {
            throw new UserException(\Yii::t('catalog', 'error_card_not_found'));
        }
        $card = catalog\Card::getId($card);

        $data = $this->getInData();
        $id = $this->getInDataVal('id', null);

        if (!$this->getInDataVal('title')) {
            throw new UserException(\Yii::t('catalog', 'error_no_field_name'));
        }
        if (!$this->getInDataVal('editor')) {
            throw new UserException(\Yii::t('catalog', 'error_no_editor_for_field'));
        }
        $oField = catalog\Card::getField($id);
        $oField->setData($data);
        $oField->entity = $card;
        $oField->save();

        $oCard = catalog\Card::get($card);
        $oCard->updCache();

        $this->actionFieldList();
    }

    /**
     * @throws UserException
     * @throws \Exception
     */
    protected function actionFieldRemove()
    {
        $id = $this->getInDataVal('id', null);

        if (!$id) {
            throw new UserException(\Yii::t('dict', 'error_field_not_found'));
        }
        $oField = catalog\Card::getField($id);

        if (in_array($oField->name, ['id', 'title'])) {
            throw new UserException(\Yii::t('dict', 'error_field_cant_removed'));
        }
        $oField->delete();
        $oCard = catalog\Card::get($oField->entity);
        $oCard->updCache();

        $this->actionFieldList();
    }

    /**
     * Форма редактирования значения для справочника.
     *
     * @throws UserException
     */
    protected function actionItemEdit()
    {
        $id = $this->getInDataVal('id', 0);
        $card = $this->getInnerData('card');

        $mItem = $id ? catalog\Dict::getValues($card, $id) : ft\Cache::getMagicTable($card)->getNewRow();

        // установка заголовка
        $oDict = catalog\Card::get($card);
        $this->setPanelName(\Yii::t('dict', 'dict_panel_name', catalog\Card::getTitle($card)));

        $aFields = $oDict->getFields();
        $aNotSortFields = [];
        foreach ($aFields as $oField) {
            if ($oField->name != catalog\Card::FIELD_SORT) {
                $aNotSortFields[] = $oField;
            }
        }

        $this->render(new ItemEdit([
            'aNotSortFields' => $aNotSortFields,
            'mItem' => $mItem,
        ]));
    }

    /**
     * Сохранение значения для справочника.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionItemSave()
    {
        $aData = $this->getInData();
        $id = $this->getInDataVal('id', 0);
        $card = $this->getInnerData('card');

        catalog\Dict::setValue($card, $aData, $id);

        $this->actionView();
    }

    /**
     * Удаление записи из справочника.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionItemRemove()
    {
        $id = $this->getInDataVal('id', 0);
        $card = $this->getInnerData('card');

        catalog\Dict::removeValue($card, $id);

        $this->actionView();
    }

    /**
     * Добавление нового справочника.
     */
    protected function actionNew()
    {
        $oCard = catalog\Card::get();
        $this->setPanelName(\Yii::t('dict', 'new_dict'));

        $this->render(new AddNewDictionary([
            'oCard' => $oCard,
        ]));
    }

    /**
     * Состояние сохранения нового справочника.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionAdd()
    {
        $aData = $this->getInData();
        $nameLayer = $this->getLayerName();
        $sDictTitle = $this->getInDataVal('title');

        if (!$sDictTitle) {
            throw new UserException(\Yii::t('dict', 'error_noname_seted'));
        }
        if (catalog\Dict::getDictByTitle($sDictTitle, $nameLayer)) {
            throw new UserException(\Yii::t('dict', 'error_name_bosy'));
        }
        $oCard = catalog\Dict::addDictionary($aData, $nameLayer);

        $this->setInnerData('card', $oCard->id);

        $this->actionView();
    }

    /**
     * Действие удаления справочника.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionRemove()
    {
        $mCardId = $this->getInnerData('card');

        if (!$mCardId) {
            throw new UserException(\Yii::t('dict', 'error_not_selected'));
        }
        if (!catalog\Card::get($mCardId)) {
            throw new UserException(\Yii::t('dict', 'error_dict_not_found'));
        }
        $aErrorMessages = [];
        catalog\Dict::removeDict($mCardId, $aErrorMessages);

        if ($aErrorMessages) {
            throw new UserException(\Yii::t('dict', 'error_del_usage_dict') . '<br>' . implode('<br>', $aErrorMessages));
        }
        $this->setInnerData('card', 0);

        $this->actionList();
    }

    /**
     * Сортировка значений справочников.
     *
     * @throws UserException
     */
    protected function actionSort()
    {
        $aItemDrop = $this->get('data');
        $aItemTarget = $this->get('dropData');
        $sOrderType = $this->get('position');

        if ($aItemDrop and $aItemTarget and $sOrderType) {
            catalog\Dict::sortValues($this->getCard(), $aItemDrop, $aItemTarget, $sOrderType);
        }

        $this->actionView();
    }

    /**
     * @param ui\state\BaseInterface $oIface
     *
     * @throws \Exception
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            // Параметр Идентификатора папки загрузки файлов модуля
            '_filebrowser_section' => FileBrowser\Api::getAliasByModule(Module::className()),
        ]);
    }

    /**
     * Сортировка для Drag & Drop полей справочника.
     */
    protected function actionSortFieldList()
    {
        $aItemDrop = $this->get('data');
        $aItemTarget = $this->get('dropData');
        $sOrderType = $this->get('position');

        if ($aItemDrop and $aItemTarget and $sOrderType) {
            catalog\model\FieldTable::sort($aItemDrop, $aItemTarget, $sOrderType);
        }

        $this->actionFieldList();
    }
}
