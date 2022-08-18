<?php

namespace skewer\build\Catalog\Collections;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\base\ui\state\BaseInterface;
use skewer\build\Catalog\Dictionary as Dict;
use skewer\build\Catalog\Goods;
use skewer\build\Cms\FileBrowser;
use skewer\components\catalog;
use skewer\components\gallery\Profile;
use skewer\components\seo;
use skewer\helpers\Transliterate;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Модуль настройки брендов
 * Class Module.
 */
class Module extends Dict\Module
{
    /** Название полей запрещенных для редактирования */
    private static $PROTECTED_FIELDS = ['title', 'alias', 'gallery', 'info', 'active', 'on_main', 'last_modified_date'];

    /** @var int число элементов на страницу */
    public $iOnPage = 20;

    /** @var int текущий номер страницы ( с 0, а приходит с 1 ) */
    public $iPage = 0;

    public function getTitle()
    {
        return \Yii::t('collections', 'module_name');
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    protected function actionTools()
    {
    }

    /**
     * Список коллекций каталога.
     */
    protected function actionList()
    {
        $this->setInnerData('card', 0);
        $this->setInnerData('rowId', 0);

        $this->iPage = $this->getInt('page');
        $iCount = 0;

        // установка заголовка
        $this->setPanelName(\Yii::t('collections', 'coll_list_for_cat'));

        $this->render(new view\Index([
            'oCollections' => catalog\Collection::getCollections($this->iPage, $this->iOnPage, $iCount),
            'page' => $this->iPage,
            'onPage' => $this->iOnPage,
            'total' => $iCount,
        ]));
    }

    /**
     * Форма редактирования параметров коллекции.
     *
     * @throws UserException
     */
    protected function actionEdit()
    {
        $oCard = catalog\Card::get();
        $this->setPanelName(\Yii::t('collections', 'new_coll'));

        // Добавить галерейный профиль для коллекций по умолчанию
        $oCard->profile_id = Profile::getDefaultId(Profile::TYPE_CATALOG4COLLECTION);

        $this->render(new view\Edit([
            'aActiveProfiles' => Profile::getActiveByType(Profile::TYPE_CATALOG4COLLECTION, true),
            'oCard' => $oCard,
        ]));
    }

    protected function actionDragAndDrop()
    {
        $card = $this->getInnerData('card');
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        $iCardId = catalog\Card::getId($card);
        $oFieldRow = $this->getFieldLink($iCardId);

        $oCardModel = catalog\Card::getModel($oFieldRow->entity);

        $oFieldFT = $oFieldRow->getFTObject();
        $oFieldFT->setModel($oCardModel);

        $iDropId = $aDropData['id'] ?? false;
        $iItemId = $this->getInDataValInt('id');
        $iIdCurrentCollection = $this->getInnerData('rowId');

        if (!$iItemId || !$iDropId || !$sPosition) {
            $this->addError('Ошибка! Неверно заданы параметры сортировки');
        }

        $oFieldFT->sortSwap($iIdCurrentCollection, $iItemId, $iDropId, $sPosition);
    }

    /**
     * Метод сохранения параметров коллекции.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionSave()
    {
        $aData = $this->getInData();

        $sDictTitle = $this->getInDataVal('title');

        if (!$sDictTitle) {
            throw new UserException(\Yii::t('collections', 'error_noname_seted'));
        }
        $oCard = catalog\Collection::addCollection($aData, $aData['profile_id']);

        $this->setInnerData('card', $oCard->id);

        $this->actionView();
    }

    /**
     * Список позиций в коллекции.
     *
     * @throws UserException
     */
    protected function actionView()
    {
        $this->setInnerData('rowId', 0);
        $title = $this->getStr('title', $this->getInnerData('title', ''));
        $active = $this->getStr('active', $this->getInnerData('active', ''));
        $card = $this->getCard();

        $this->iPage = $this->getInt('page');
        $iCount = 0;

        // получение контентных данных
        $oTable = ft\Cache::getMagicTable($card);
        if (!$oTable) {
            throw new UserException(\Yii::t('collections', 'error_dict_not_found'));
        }
        // набор объектов на вывод
        $query = $oTable->find()->asArray();
        if ($title) {
            $query->where('title LIKE ?', '%' . $title . '%');
        }
        if ($active) {
            $query->where('active', $active == 1);
        }

        $query->setCounterRef($iCount)
            ->limit($this->iOnPage, ($this->iPage * $this->iOnPage));

        $this->setInnerData('rowId', 0);
        // установка заголовка
        $this->setPanelName(\Yii::t('collections', 'coll_panel_name', catalog\Card::getTitle($card)));

        $this->render(new view\View([
            'sTitle' => $title,
            'bActive' => $active,
            'aValues' => $query->getAll(),
            'page' => $this->iPage,
            'onPage' => $this->iOnPage,
            'total' => $iCount,
            'sCardTitle' => catalog\Card::getTitle($card),
        ]));
    }

    /**
     * Форма редактирования значения для справочника.
     *
     * @throws UserException
     */
    protected function actionItemEdit()
    {
        $id = $this->getInDataVal('id', $this->getInnerData('rowId', 0));
        $this->setInnerData('rowId', $id);
        $card = $this->getInnerData('card');

        if (!$card) {
            throw new UserException('Card not found!');
        }
        $oTable = ft\Cache::getMagicTable($card);
        if (!$oTable) {
            throw new UserException(\Yii::t('collections', 'error_dict_not_found'));
        }
        $oItem = $id ? $oTable->find($id) : $oTable->getNewRow();

        if (!$oItem) {
            throw new UserException("Can not find good id = [{$id}]");
        }
        // def values
        if (!$id) {
            $oItem->title = '';
            $oItem->active = 1;
        }

        // установка заголовка
        $oDict = catalog\Card::get($card);
        $this->setPanelName(\Yii::t('collections', 'dict_panel_name', catalog\Card::getTitle($card)));

        $aFields = $oDict->getFields();

        $oEntityRow = catalog\Entity::get($oItem->getModel()->getName());
        $iCollectionId = $oEntityRow->id;
        $oSeoComponent = new SeoElementCollection($oItem->id, $iCollectionId, $oItem->getData(), $oItem->getModel()->getName());

        $this->render(new view\ItemEdit([
            'aFields' => $aFields,
            'oItem' => $oItem,
            'sClassName' => SeoElementCollection::className(),
            'sEntityIdVal' => $oItem->id . ':' . $oItem->getModel()->getName(),
            'id' => $id,
            'oSeoElemCollection' => $oSeoComponent,
        ]));
    }

    /**
     *  Сохранение значения всех полей для коллекции.
     *
     * @throws UserException
     * @throws \skewer\base\ui\ARSaveException
     */
    protected function actionItemSave()
    {
        $aData = $this->getInData();
        $id = $this->getInDataVal('id', 0);
        $card = $this->getInnerData('card');

        if (!$card) {
            throw new UserException('Card not found!');
        }
        $oTable = ft\Cache::getMagicTable($card);
        if (!$oTable) {
            throw new UserException(\Yii::t('collections', 'error_dict_not_found'));
        }
        $oItem = $id ? $oTable->find($id) : $oTable->getNewRow();
        $aOldAtrributes = $oItem->getData();

        $oItem->setData($aData);

        if (!$oItem->title) {
            throw new UserException(\Yii::t('collections', 'error_title_not_found'));
        }
        // генерация alias для коллекции
        $oItem->alias = (!$oItem->alias) ? Transliterate::generateAlias($oItem->title) : Transliterate::generateAlias($oItem->alias);
        $iSectionId = catalog\Section::get4Collection($card);

        $oItem->alias = seo\Service::generateAlias($oItem->alias, $oItem->id, $iSectionId, 'CollectionViewer_' . $oItem->getModel()->getName());
        $oItem->setVal('last_modified_date', date('Y-m-d H:i:s', time()));

        if ($oItem->save()) {
            $oEntityRow = catalog\Entity::get($oItem->getModel()->getName());
            $iCollectionId = $oEntityRow->id;

            seo\Api::saveJSData(
                new SeoElementCollection(ArrayHelper::getValue($aOldAtrributes, 'id', 0), $iCollectionId, $aOldAtrributes, $oItem->getModel()->getName()),
                new SeoElementCollection($oItem->id, $iCollectionId, $oItem->getData(), $oItem->getModel()->getName()),
                $aData,
                0,
                false
            );

            $oSearch = new Search();
            $oSearch->setCard($oTable->getName());
            $oSearch->updateByObjectId($oItem->id);

            if (seo\Service::$bAliasChanged) {
                $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $oItem->alias]));
            }

            $this->actionView();
        } else {
            $this->addError($oItem->getError());
        }
    }

    /**
     * Действие: Удаление элемента коллекции.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionItemRemove()
    {
        $id = $this->getInDataVal('id', 0);
        $card = $this->getInnerData('card');

        $this->setInnerData('rowId', 0);

        if (!$id) {
            throw new UserException(\Yii::t('collections', 'error_row_not_found'));
        }
        $oCurTable = ft\Cache::getMagicTable($card);
        if (!$oCurTable) {
            throw new UserException(\Yii::t('collections', 'error_dict_not_found'));
        }
        catalog\Dict::removeValue($card, $id);

        $oSearch = new Search();
        $oSearch->setCard($oCurTable->getName());
        $oSearch->deleteByObjectId($id);

        $this->actionView();
    }

    protected function actionDelete()
    {
        $aData = $this->get('data');
        $card = $this->getInnerData('card');

        $oCurTable = ft\Cache::getMagicTable($card);
        if (!$oCurTable) {
            throw new UserException(\Yii::t('collections', 'error_dict_not_found'));
        }
        foreach ($aData['items'] as $item) {
            catalog\Dict::removeValue($card, $item['id']);

            $oSearch = new Search();
            $oSearch->setCard($oCurTable->getName());
            $oSearch->deleteByObjectId($item['id']);
        }

        $this->actionView();
    }

    /**
     * @param $card
     *
     * @return catalog\model\FieldRow
     */
    protected function getFieldLink($card)
    {
        $field = catalog\model\FieldTable::find()
            ->where('link_id', $card)
            ->getOne();

        return $field;
    }

    /**
     * Генерация уникального алиаса для сущности.
     *
     * @param $card
     * @param $alias
     * @param $id
     *
     * @throws ft\Exception
     * @throws \Exception
     *
     * @return array|string
     */
    private function getUniqueAlias($card, $alias, $id)
    {
        $model = ft\Cache::get($card);

        $alias = Transliterate::generateAlias($alias);

        $flag = (bool) Query::SelectFrom($model->getTableName())
            ->where('alias', $alias)
            ->andWhere('id!=?', $id)
            ->getCount();

        if (!$flag) {
            return $alias;
        }

        preg_match('/^(\S+)(-\d+)?$/Uis', $alias, $res);
        $aliasTpl = $res[1] ?? $alias;
        $iCnt = isset($res[2]) ? -(int) $res[2] : 0;
        while (mb_substr($aliasTpl, -1) == '-') {
            $aliasTpl = mb_substr($aliasTpl, 0, mb_strlen($aliasTpl) - 1);
        }

        do {
            ++$iCnt;
            $alias = $aliasTpl . '-' . $iCnt;

            $flag = (bool) Query::SelectFrom($model->getTableName())
                ->where('alias', $alias)
                ->andWhere('id!=?', $id)
                ->getCount();
        } while ($flag);

        return $alias;
    }

    /**
     * Метод сохранения значения поля для коллекции (редактирование из списка).
     *
     * @throws UserException
     */
    protected function actionItemFastSave()
    {
        $data = $this->get('data');
        $field = $this->get('field_name');
        $card = $this->getInnerData('card');

        $id = ArrayHelper::getValue($data, 'id', 0);
        $value = ArrayHelper::getValue($data, $field, '');

        if (!$id) {
            throw new UserException('Card not found!');
        }
        if (!$card) {
            throw new UserException('Card not found!');
        }
        $oTable = ft\Cache::getMagicTable($card);
        if (!$oTable) {
            throw new UserException(\Yii::t('collections', 'error_dict_not_found'));
        }
        $oItem = $id ? $oTable->find($id) : $oTable->getNewRow();

        $oItem->setData([$field => $value]);

        if ($oItem->save()) {
            $oSearch = new Search();
            $oSearch->setCard($oTable->getName());
            $oSearch->updateByObjectId($oItem->id);
            \Yii::$app->router->updateModificationDateSite();

            $this->actionView();
        } else {
            $this->addError($oItem->getError());
        }
    }

    /**
     * Список товаров связанных с коллекцией.
     *
     * @throws UserException
     */
    protected function actionGoodsView()
    {
        $id = $this->getInnerData('rowId', 0);
        $card = $this->getInnerData('card');

        $iCardId = catalog\Card::getId($card);
        $field = $this->getFieldLink($iCardId);

        $items = [];

        if ($field) {
            $items = catalog\GoodsSelector::getList4Collection($iCardId, $field->name, $id)->parse();
        }

        $oTable = ft\Cache::getMagicTable($card);
        if (!$oTable) {
            throw new UserException(\Yii::t('collections', 'error_dict_not_found'));
        }
        $oItem = $id ? $oTable->find($id) : $oTable->getNewRow();

        // установка заголовка
        //$this->setPanelName( \Yii::t('collections', 'coll_list_for_cat') );
        $this->setPanelName(\Yii::t('collections', 'goods_coll_list_panel', [$oItem->title, catalog\Card::getTitle($card)]));

        $this->render(new view\GoodsView([
            'aItems' => $items,
            'editor' => $field ? $field->editor : '',
        ]));
    }

    /**
     * Интерфейс редактиования коллекции.
     */
    protected function actionEditCollection()
    {
        $card = $this->getInnerData('card');
        $oDict = catalog\Card::get($card);

        $aProfiles = Profile::getActiveByType(Profile::TYPE_CATALOG4COLLECTION, true);

        // Добавить использующийся профиль галереи в независимости от его активности
        foreach ($oDict->getFields() as $oField) {
            if ($oField->editor == ft\Editor::GALLERY) {
                $oDict->profile_id = $iProfileId = $oField->link_id;

                if ($aProfileCurrent = Profile::getById($iProfileId)) {
                    $aProfiles[$iProfileId] = $aProfileCurrent['title'];
                }
                break;
            }
        }

        $this->render(new view\EditCollection([
            'aProfiles' => $aProfiles,
            'oDict' => $oDict,
        ]));
    }

    /**
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

        $sHeadText = \Yii::t('collections', 'dict_panel_name', $oCard->title);
        $this->setPanelName(\Yii::t('collections', 'title_field_list', $oCard->title));

        $aFields = $oCard->getFields();
        foreach ($aFields as $iKey => $aField) {
            if (in_array($aField->name, self::$PROTECTED_FIELDS)) {
                unset($aFields[$iKey]);
            }
        }
        $this->render(new view\Structure([
            'sHeadText' => $sHeadText,
            'aFields' => $aFields,
        ]));
    }

    /**
     * Состояние сохранения отредактированной коллекции.
     *
     * @throws \Exception
     * @throws UserException
     */
    protected function actionSaveCollection()
    {
        $aData = $this->getInData();
//        $type = ArrayHelper::getValue( $aData, 'type', '' );
//        $parent = ArrayHelper::getValue( $aData, 'parent', '' );

        $card = $this->getInnerData('card');

        if (!($title = ArrayHelper::getValue($aData, 'title', ''))) {
            throw new UserException(\Yii::t('collections', 'error_no_card_name'));
        }
//        if ( $type != catalog\Card::TypeBasic && !$parent )
//            $this->riseError( \Yii::t('collections', 'error_no_base_card') );

        // всегда только расширенная карточка
//        if ( $type != catalog\Card::TypeBasic )
//            $aData['type'] = catalog\Card::TypeExtended;

        $oDict = catalog\Card::get($card);

        // Сохранить профиль галереи коллекции
        foreach ($oDict->getFields() as $oField) {
            if ($oField->editor == ft\Editor::GALLERY) {
                $oField->link_id = $aData['profile_id'];
                $oField->save();
                break;
            }
        }

        $oDict->setData(['title' => $title]);
        $oDict->save();
        $oDict->updCache();

        $this->actionView();
    }

    /**
     * {@inheritdoc}
     */
    protected function actionRemove()
    {
        $aData = $this->get('data');
        $mCardId = $aData['id'];

        if (!$mCardId) {
            throw new UserException(\Yii::t('collections', 'error_coll_not_selected'));
        }
        if (!$oCard = catalog\Card::get($mCardId)) {
            throw new UserException(\Yii::t('collections', 'error_coll_not_found'));
        }
        /* Проверим есть ли разделы этой коллекции*/
        if (Api::getCountCollectionSections($oCard->id)) {
            throw new UserException(\Yii::t('collections', 'collection_in_use'));
        }
        /** Имя карточки удаляемой коллекции */
        $sCollCardName = $oCard->name;

        $aErrorMessages = [];
        catalog\Dict::removeDict($mCardId, $aErrorMessages);

        if ($aErrorMessages) {
            throw new UserException(\Yii::t('collections', 'error_del_usage_coll') . '<br>' . implode('<br>', $aErrorMessages));
        }
        // Удалить из поискового индекса
        $oSearch = new Search();
        $oSearch->setCard($sCollCardName);
        $oSearch->deleteAll();

        $this->setInnerData('card', 0);

        $this->actionList();
    }

    /**
     * @param BaseInterface $oIface
     *
     * @throws \Exception
     */
    protected function setServiceData(BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            // Параметр Идентификатора папки загрузки файлов модуля
            '_filebrowser_section' => FileBrowser\Api::getAliasByModule(Goods\Module::className()),
        ]);
    }
}
