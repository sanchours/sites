<?php

namespace skewer\build\Catalog\Goods;

use skewer\base\log\models\Log;
use skewer\base\orm\Query;
use skewer\base\section\Tree;
use skewer\base\SysVar;
use skewer\base\Twig;
use skewer\base\ui\state\BaseInterface;
use skewer\build\Catalog\Goods\view\LoadSection;
use skewer\build\Catalog\Goods\view\SetCard;
use skewer\build\Catalog\LeftList\ModulePrototype;
use skewer\build\Cms\FileBrowser;
use skewer\components\catalog;
use skewer\components\ext\ListRows;
use skewer\components\seo\Api;
use skewer\components\seo\Service;
use skewer\helpers\Files;
use yii\helpers\ArrayHelper;

/**
 * Каталог. Админка.
 * Class Module.
 */
class Module extends ModulePrototype
{
    const categoryField = '__category';
    const mainLinkField = '__main_link';

    /** @var int число элементов на страницу */
    public $iOnPage = 30;

    /** @var int текущий номер страницы ( с 0, а приходит с 1 ) */
    public $iPage = 0;

    /** @var array Данные для фильтрации списка товаров, чтобы исключить выбор самих себя */
    public $aFilterData = [];

    private $aTableDel = ['co_base_card', 'c_goods', 'cl_section', 'cl_semantic'];

    /**
     * Получение идентификатора текущего раздела.
     *
     * @return int|mixed
     */
    public function getCurrentSection()
    {
        $section = $this->getFilterSection();
        if (!$section) {
            $section = $this->sectionId();
        }

        return $section;
    }

    /**
     * Возвращает имя текущей каталожной карточки.
     *
     * @return string
     */
    public function getCardName()
    {
        return catalog\Section::getDefCard($this->getCurrentSection());
    }

    protected function preExecute()
    {
        $this->iPage = $this->getInt('page', $this->getInnerData('page', 0));
        $this->setInnerData('page', $this->iPage);
    }

    /**
     * Получение значений полей фильтра.
     *
     * @param string[] $aFilterFields Список полей фильтра
     *
     * @return array
     */
    protected function getFilterVal($aFilterFields)
    {
        $aFilter = [];

        foreach ($aFilterFields  as $sField) {
            $sName = 'filter_' . $sField;
            $sVal = $this->getStr($sName, $this->getInnerData($sName, ''));
            $this->setInnerData($sName, $sVal);
            $aFilter[$sField] = $sVal;
        }

        return $aFilter;
    }

    /**
     * Возвращает значения поля раздел из фильтра.
     *
     * @return mixed
     */
    public function getFilterSection()
    {
        return ArrayHelper::getValue($this->getFilterVal(['section']), 'section', 0);
    }

    /**
     * Набор выделенных позиций при мультивыделении в списке.
     *
     * @return array
     */
    protected function getMultipleData()
    {
        $aData = $this->get('data');
        $aItems = [];

        if ($this->getInDataVal('multiple')) {
            if (isset($aData['items']) && is_array($aData['items'])) {
                foreach ($aData['items'] as $aItem) {
                    $aItems[] = ArrayHelper::getValue($aItem, 'id', 0);
                }
            }
        } else {
            if ($id = $this->getInDataValInt('id')) {
                $aItems = [$id];
            }
        }

        return $aItems;
    }

    protected function actionInit()
    {
        $this->iOnPage = SysVar::get('catalog.countShowGoods');
        if (!$this->iOnPage) {
            $this->iOnPage = 30;
        }

        $this->sectionId = 0;

        $iInitParam = (int) $this->get('init_param');

        if ($iInitParam) {
            $this->actionEdit($iInitParam);
        } else {
            $this->actionList();
        }
    }

    /**
     * Интерфейс задания карточки для категории.
     */
    public function actionSetCard()
    {
        $this->setPanelName(\Yii::t('catalog', 'goods_card_select'));

        $this->render(new SetCard([
            'aGoodsCardList' => catalog\Card::getGoodsCardList(true),
            'sCardName' => $this->getCardName(),
        ]));
    }

    /**
     * Состояние сохранения карточки для категории.
     */
    public function actionSaveCard()
    {
        if (!($sCardName = $this->getInDataVal('card'))) {
            $this->addError(\Yii::t('catalog', 'error_no_card'));
            $this->actionSetCard();
        } else {
            catalog\Section::setDefCard($this->getCurrentSection(), $sCardName);

            if (isset($this->defCard)) {
                $this->defCard = $sCardName;
            }

            $this->actionInit();
        }
    }

    protected function actionList()
    {
        $this->setInnerData('currentGoods', 0);
        $this->setPanelName(\Yii::t('catalog', 'goods_list'));

        $model = model\SectionList::get($this->getCurrentSection())
            ->setFilter($this->getFilterVal(['article', 'title', 'price', 'active']))
            ->limit($this->iPage, $this->iOnPage);

        $this->render(new view\SectionList([
            'model' => $model,
        ]));
    }

    /**
     * Сортировка товарных позиций внутри раздела.
     */
    protected function actionSort()
    {
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        $iDropId = $aDropData['id'] ?? false;
        $aItems = $this->getMultipleData();

        if (!count($aItems) || !$iDropId || !$sPosition) {
            $this->addError('Ошибка! Неверно заданы параметры сортировки');
        }

        if ($sPosition == 'after') {
            $aItems = array_reverse($aItems);
        }

        foreach ($aItems as $iSelectId) {
            catalog\model\SectionTable::sortSwap($this->getCurrentSection(), $iSelectId, $iDropId, $sPosition);
        }
    }

    /**
     * Сортировка товарных позиций внутри родительского товара.
     */
    protected function actionSortModific()
    {
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        $iDropId = $aDropData['id'] ?? false;
        $aItems = $this->getMultipleData();

        if (!count($aItems) || !$iDropId || !$sPosition) {
            $this->addError('Ошибка! Неверно заданы параметры сортировки');
        }

        if ($sPosition == 'after') {
            $aItems = array_reverse($aItems);
        }

        $parentId = $this->getInnerData('currentGoods');

        foreach ($aItems as $iSelectId) {
            catalog\model\GoodsTable::sortSwap($parentId, $iSelectId, $iDropId, $sPosition);
        }
    }

    /**
     * Сортировка товарных позиций внутри раздела.
     */
    protected function actionSortRelated()
    {
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');
        $idRelated = $this->getInnerData('currentGoods');
        $iDropId = $aDropData['id'] ?? false;
        $aItems = $this->getMultipleData();

        if (!count($aItems) || !$iDropId || !$sPosition) {
            $this->addError('Ошибка! Неверно заданы параметры сортировки');
        }

        if ($sPosition == 'after') {
            $aItems = array_reverse($aItems);
        }

        foreach ($aItems as $iSelectId) {
            catalog\model\SemanticTable::sortSwapRelated($idRelated, $iSelectId, $iDropId, $sPosition);
        }
    }

    /**
     * Установка товарной позиции на первое место в списке.
     */
    protected function actionSetFirst()
    {
        $aData = $this->getInData();

        if (!isset($aData['id']) || !$aData['id']) {
            $this->addError('Ошибка! Неверно заданы параметры сортировки');
        }

        catalog\model\SectionTable::sortUp($this->getCurrentSection(), $aData['id']);

        $this->actionList();
    }

    protected function actionEdit($iId = 0)
    {
        $aData = $iId ? ['id' => $iId] : $this->get('data');

        /** @var model\GoodsEditor $model */
        $model = model\GoodsEditor::get()
            ->setSectionData($this->getCurrentSection(), $this->getCardName(), $iId ?: $this->getInnerData('currentGoods'))
            ->setData($aData, $this->get('from'));
        $sFieldName = $this->get('field_name');
        $model->setUpdField($sFieldName);

        $aModelData = $model->getData();

        $currentGoods = $this->getInnerData('currentGoods');
        $bIsNewGood = (empty($aModelData['id'])) && (empty($currentGoods));

        $aOldData = $bIsNewGood
            ? SeoGood::getBlankGood($this->getCardName())
            : catalog\GoodsSelector::get(!empty($aModelData['id']) ? $aModelData['id'] : $this->getInnerData('currentGoods'));

        if ($model->save()) {
            /** @var bool $bIsCommonList Это общий список товаров(Фильтр = Раздел:Все)? */
            $bIsCommonList = !(bool) $this->getCurrentSection();

            if (!$bIsCommonList) {
                $oOldSeo = new SeoGood(ArrayHelper::getValue($aOldData, 'id', 0), $this->getCurrentSection(), $aOldData);
                $oOldSeo->setExtraAlias($this->getCardName());

                $aGood = catalog\GoodsSelector::get($model->getGoodsRow()->getRowId());
                $oNewSeo = new SeoGood(ArrayHelper::getValue($aGood, 'id', 0), $this->getCurrentSection(), $aGood);
                $oNewSeo->setExtraAlias($this->getCardName());

                Api::saveJSData($oOldSeo, $oNewSeo, $model->getData(), $this->getCurrentSection());
            }

            $aItem = $model->getGoodsRow()->getData();

            if (Service::$bAliasChanged) {
                $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $aItem['alias']]));
            }

            if ($sFieldName) {
                if (isset($aItem['price'])) {
                    $aItem['price'] = Twig::priceFormat($aItem['price'], 0);
                }
                $oListVals = new ListRows();
                $oListVals->setSearchField(['id']);
                $oListVals->addDataRow($aItem);
                $oListVals->setData($this);
            } else {
                $this->actionList();
            }
        } else {
            if ($error = $model->getErrorMsg()) {
                $this->addError($error);
            }

            if (isset($aData['price'])) {
                $model->getGoodsRow()->getBaseRow()->setVal('price', $aData['price']);
            }

            if (!$bIsNewGood) {
                if (isset($aOldData['title'])) {
                    $sPanelName = \Yii::t('catalog', 'good_editor', $aOldData['title']);
                } else {
                    $aData = $model->getGoodsRow()->getData();
                    $sPanelName = \Yii::t('catalog', 'good_editor', $aData['title']);
                }
            } else {
                $sPanelName = \Yii::t('catalog', 'good_editor_new');
            }

            $this->setPanelName($sPanelName);

            $this->setInnerData('currentGoods', $model->getGoodsRow()->getRowId());
            $this->setInnerData('page', $this->iPage);

            $this->setInnerData('viewClass', view\GoodsEditor::className());
            view\GoodsEditor::$bOnlyActive = true;
            $this->render(new view\GoodsEditor([
                'model' => $model,
            ]));
        }
    }

    /**
     * Метод, меняющий на лету значения поля "Основной раздел" при
     * изменении поля "Категория".
     */
    protected function actionLoadSection()
    {
        $aFormData = $this->get('formData', []);
        $mSectionList = isset($aFormData[self::categoryField]) ? $aFormData[self::categoryField] : '';
        $iSection = isset($aFormData[self::mainLinkField]) ? $aFormData[self::mainLinkField] : 0;

        $aSectionKeys = explode(',', $mSectionList);
        $aSectionList = Tree::getSectionsTitle($aSectionKeys);

        if (count($aSectionKeys) && (!$iSection || !in_array($iSection, $aSectionKeys))) {
            $iSection = array_shift($aSectionKeys);
        }

        $loadSection = new LoadSection([
            'sMainLinkField' => self::mainLinkField,
            'aSectionList' => $aSectionList,
            'iSection' => $iSection,
        ]);
        $loadSection->build();
        $this->setInterfaceUpd($loadSection->getInterface());
    }

    /**
     * Дублирование товара.
     */
    protected function actionClone()
    {
        /** @var model\GoodsEditor $model */
        $model = model\GoodsEditor::get()
            ->setSectionData($this->getCurrentSection(), '')
            ->setData($this->get('data'), $this->get('from'));

        if ($id = $model->saveNewCopy()) {
            if (SysVar::get('catalog.copy_modification') && SysVar::get('catalog.goods_modifications')) {
                //Копирование модификаций
                $iOldParentId = $this->get('data')['id'];
                $iNewParentId = $model->getGoodsRow()->getRowId();

                $aGoodsMod = Query::SelectFrom('c_goods')
                    ->fields(['base_id'])
                    ->where('parent', $iOldParentId)
                    ->andWhere('base_id <> ?', $iOldParentId)
                    ->asArray()
                    ->getAll();

                foreach ($aGoodsMod as $aItem) {
                    $oModModel = model\ModGoodsEditor::get($aItem['base_id']); //Получим все данные модификации
                    $data = $oModModel->getMainObject()->getData();
                    $data['id'] = 0; //Запишем как новый
                    $oModModel->setData($data); //Делаем имитацию будто данные пришли с формы
                    $oModModel->saveNewCopy($iNewParentId);
                }
            }

            $this->actionList();
        } else {
            if ($error = $model->getErrorMsg()) {
                $this->addError($error);
            }
        }
    }

    /**
     * Удаление записи.
     */
    public function actionDelete()
    {
        /** @var model\GoodsEditor $model */
        $model = model\GoodsEditor::get()
            ->setSectionData($this->getCurrentSection(), '')
            ->setData($this->get('data'), $this->get('from'));

        if ($model->multipleDelete()) {
            $this->actionList();
        } else {
            if ($error = $model->getErrorMsg()) {
                $this->addError($error);
            }
        }
    }

    /**
     * Удалнение всех товаров.
     */
    public function actionDeleteAll()
    {
        // добавить в лог сообщение о редактировании
        Log::addNoticeReport(
            \Yii::t('adm', 'delAllLog'),
            \Yii::t('adm', 'delAllLog'),
            Log::logUsers,
            $this->getModuleName()
        );

        //удаляем все связи
        $sQueryDel1 = "DELETE FROM seo_data WHERE `group` = 'good'";
        Query::SQL($sQueryDel1);

        $sQueryDel2 = 'DELETE photogallery_albums, photogallery_photos
                      FROM co_base_card
                      LEFT JOIN photogallery_albums ON photogallery_albums.id = co_base_card.gallery
                      LEFT JOIN photogallery_photos ON photogallery_photos.album_id = co_base_card.gallery';
        Query::SQL($sQueryDel2);

        /*Уничтожаем записи о товарах в поисковом индексе*/
        $sQueryDel3 = "DELETE FROM search_index WHERE `class_name`='CatalogViewer';";
        Query::SQL($sQueryDel3);

        /*Уничтожаем все отзывы к товарам */
        $sQueryDel3 = "DELETE FROM `guest_book` WHERE `parent_class`='GoodsReviews';";
        Query::SQL($sQueryDel3);

        //удаляем галереи
        $sQuerySelect = 'SELECT DISTINCT gallery FROM co_base_card';
        $returnSel = Query::SQL($sQuerySelect);
        while ($fetch = $returnSel->fetchArray()) {
            if ($fetch['gallery']) {
                $sPath = '../web/files/gallery/' . $fetch['gallery'] . '/';
                Files::delDirectoryRec($sPath);
            }
        }
        //очистка таблиц со связями
        foreach ($this->aTableDel as $sTable) {
            $qClean = 'DELETE FROM ' . $sTable;
            Query::SQL($qClean);
        }

        //удаление дополнительных данных товара из карточек
        $oCardGood = catalog\Card::getGoodsCards();
        foreach ($oCardGood as $row) {
            $sClean = 'ce_' . $row->name;
            $qClean = 'DELETE FROM ' . $sClean;
            Query::SQL($qClean);
        }
        $this->actionList();
    }

    // Сопутствующие
    // -- RELATED --
    public function actionRelatedItems()
    {
        $this->setPanelName(\Yii::t('catalog', 'relatedItems_title'));

        $this->setInnerData('filter_section', $this->sectionId());

        $model = model\RelatedList::get($this->getInnerData('currentGoods'))
            ->limit($this->iPage, $this->iOnPage);

        $this->render(new view\RelatedList([
            'model' => $model,
        ]));
    }

    public function actionAddRelatedItem()
    {
        $this->setPanelName(\Yii::t('catalog', 'relatedItems_add_title'));

        $model = model\FullCustomList::get($this->getStr('filter_section', $this->getInnerData('filter_section', 0)))
            ->getWithoutRelated($this->getInnerData('currentGoods'))
            ->setFilter($this->getFilterVal(['title', 'section']))
            ->limit($this->iPage, $this->iOnPage);

        $this->render(new view\AddRelatedList([
            'model' => $model,
        ]));
    }

    /**
     * Добавление сопутствующего товара.
     */
    public function actionLinkRelatedItem()
    {
        $data = $this->getInData();
        $list = [];

        if ($this->getInDataVal('multiple')) {
            $items = ArrayHelper::getValue($data, 'items', []);
            if ($items) {
                foreach ($items as $item) {
                    $list[] = $item['id'] ?? 0;
                }
            }
        } else {
            if ($id = $this->getInDataVal('id')) {
                $list[] = $id;
            }
        }

        /*Сброс фильтра по секции*/
        $this->setInnerData('filter_section', $this->sectionId());

        if (count($list)) {
            foreach ($list as $id) {
                if ($id) {
                    $iPriority = catalog\model\SemanticTable::priorityRelated($this->getInnerData('currentGoods'), catalog\Semantic::TYPE_RELATED);
                    catalog\model\SemanticTable::link(catalog\Semantic::TYPE_RELATED, $this->getInnerData('currentGoods'), 1, $id, 1, $iPriority);
                }
            }
        }

        $this->actionRelatedItems();
    }

    public function actionRemoveRelatedItem()
    {
        $data = $this->getInData();
        $list = [];

        if ($this->getInDataVal('multiple')) {
            $items = ArrayHelper::getValue($data, 'items', []);
            if ($items) {
                foreach ($items as $aItem) {
                    $list[] = $aItem['id'] ?? 0;
                }
            }
        } else {
            if ($id = $this->getInDataVal('id')) {
                $list[] = $id;
            }
        }

        if (count($list)) {
            foreach ($list as $id) {
                if ($id) {
                    catalog\model\SemanticTable::unlink(catalog\Semantic::TYPE_RELATED, $id, 1, $this->getInnerData('currentGoods'), 1);
                }
            }
        }

        $this->actionRelatedItems();
    }

    // В комплекте
    // -- INCLUDED --
    public function actionIncludedItems()
    {
        $this->setPanelName(\Yii::t('catalog', 'includedItems_title'));

        $this->setInnerData('filter_section', $this->sectionId());

        $model = model\IncludedList::get($this->getInnerData('currentGoods'))
            ->limit($this->iPage, $this->iOnPage);

        $this->render(new view\IncludedList([
            'model' => $model,
        ]));
    }

    public function actionAddIncludedItem()
    {
        $this->setPanelName(\Yii::t('catalog', 'includedItems_add_title'));

        $model = model\FullCustomList::get($this->getStr('filter_section', $this->getInnerData('filter_section', 0)))
            ->getWithoutIncluded($this->getInnerData('currentGoods'))
            ->setFilter($this->getFilterVal(['title', 'section']))
            ->limit($this->iPage, $this->iOnPage);

        $this->render(new view\AddIncludedList([
            'model' => $model,
        ]));
    }

    public function actionLinkIncludedItem()
    {
        $data = $this->getInData();
        $list = [];

        if ($this->getInDataVal('multiple')) {
            $items = ArrayHelper::getValue($data, 'items', []);
            if ($items) {
                foreach ($items as $item) {
                    $list[] = $item['id'] ?? 0;
                }
            }
        } else {
            if ($id = $this->getInDataVal('id')) {
                $list[] = $id;
            }
        }

        /*Сброс фильтра по секции*/
        $this->setInnerData('filter_section', $this->sectionId());

        if (count($list)) {
            foreach ($list as $id) {
                if ($id) {
                    $iPriority = catalog\model\SemanticTable::priorityRelated($this->getInnerData('currentGoods'), catalog\Semantic::TYPE_INCLUDE);
                    catalog\model\SemanticTable::link(catalog\Semantic::TYPE_INCLUDE, $this->getInnerData('currentGoods'), 1, $id, 1, $iPriority);
                }
            }
        }

        $this->actionIncludedItems();
    }

    public function actionRemoveIncludedItem()
    {
        $data = $this->getInData();
        $list = [];

        if ($this->getInDataVal('multiple')) {
            $items = ArrayHelper::getValue($data, 'items', []);
            if ($items) {
                foreach ($items as $aItem) {
                    $list[] = $aItem['id'] ?? 0;
                }
            }
        } else {
            if ($id = $this->getInDataVal('id')) {
                $list[] = $id;
            }
        }

        if (count($list)) {
            foreach ($list as $id) {
                if ($id) {
                    catalog\model\SemanticTable::unlink(catalog\Semantic::TYPE_INCLUDE, $this->getInnerData('currentGoods'), 1, $id, 1);
                }
            }
        }

        $this->actionIncludedItems();
    }

    // Модификации
    // -- Modifications --
    public function actionModificationsItems()
    {
        // получаем идентификатор основного товара
        if (!($id = $this->getInnerData('currentGoods'))) {
            $aData = $this->get('data');
            if (isset($aData['id']) && $aData['id']) {
                $id = $aData['id'];
                $this->setInnerData('currentGoods', $id);
            }
        }

        $model = model\ModificList::get($id)
            ->limit($this->iPage, $this->iOnPage);

        $data = $model->getCurrentObject()->getData();
        $this->setPanelName(\Yii::t('catalog', 'modificationsItems_title') . ' "' . $data['title'] . '"');

        $this->render(new view\ModificList([
            'model' => $model,
        ]));
    }

    public function actionEditModificationsItem()
    {
        $aData = $this->get('data');

        /** @var model\ModGoodsEditor $model */
        $model = model\ModGoodsEditor::get($this->getInnerData('currentGoods'))
            ->setData($aData, $this->get('from'))
            ->setUpdField($this->get('field_name'));

        $data = $model->getMainObject()->getData();
        $this->setPanelName(\Yii::t('catalog', 'modificationsItems_edit_title') . ' "' . $data['title'] . '"');

        $aModelData = $model->getData();

        $bIsNewGood = empty($aModelData['id']);

        $aOldData = $bIsNewGood
            ? SeoGood::getBlankGood($model->getMainObject()->getExtCardName())
            : catalog\GoodsSelector::get($aModelData['id']);

        if ($model->save()) {
            /** @var bool $bIsCommonList Это общий список товаров(Фильтр = Раздел:Все)? */
            $bIsCommonList = !(bool) $this->getCurrentSection();

            if (!$bIsCommonList) {
                $aGood = catalog\GoodsSelector::get($model->getGoodsRow()->getRowId());

                Api::saveJSData(
                    new SeoGoodModifications(ArrayHelper::getValue($aOldData, 'id', 0), $this->getCurrentSection(), $aOldData),
                    new SeoGoodModifications(ArrayHelper::getValue($aGood, 'id', 0), $this->getCurrentSection(), $aGood),
                    $model->getData(),
                    $this->getCurrentSection()
                );
            }

            $this->actionModificationsItems();
        } else {
            view\ModGoodsEditor::$bOnlyActive = true;

            if ($error = $model->getErrorMsg()) {
                $this->addError($error);
            }
            $this->setInnerData('viewClass', view\ModGoodsEditor::className());
            $this->render(new view\ModGoodsEditor([
                'model' => $model,
            ]));
        }
    }

    /**
     * @throws \Exception
     */
    public function actionDeleteModificationsItem()
    {
        $model = model\ModGoodsEditor::get($this->getInnerData('currentGoods'))
            ->setData($this->get('data'), $this->get('from'));

        if ($model->multipleDelete()) {
            $this->actionModificationsItems();
        } else {
            if ($error = $model->getErrorMsg()) {
                $this->addError($error);
            }
        }
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
            '_filebrowser_section' => FileBrowser\Api::getAliasByModule(self::className()),
        ]);
    }
}
