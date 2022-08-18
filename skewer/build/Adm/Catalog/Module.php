<?php

namespace skewer\build\Adm\Catalog;

use skewer\base\section;
use skewer\base\site\Layer;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Catalog\Collections\Search;
use skewer\build\Catalog\Goods;
use skewer\build\Page\CatalogViewer;
use skewer\build\Page\RecentlyViewed;
use skewer\components\catalog;
use skewer\components\search\GetEngineEvent;
use skewer\components\seo\Service;
use yii\base\UserException;

/**
 * Модуль настройки вывода каталога в разделе
 * Class Module.
 */
class Module extends Goods\Module implements site_module\SectionModuleInterface
{
    /** @var int Карточка для поисковой станицы */
    public $searchCard = false;

    /** @var int Поле для страницы с коллекциями */
    public $collectionField = false;

    /** @var string Карточка для создания нового товара */
    public $defCard = false;

    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page', $this->getInnerData('page', 0));
        $this->setInnerData('page', $this->iPage);
    }

    /**
     * Точка входа.
     */
    protected function actionInit()
    {
        $iInitParam = (int) $this->get('init_param');
        if ($iInitParam) {
            $this->actionEdit($iInitParam);

            return;
        }

        // постраничник
        $this->iOnPage = SysVar::get('catalog.countShowGoods');
        if (!$this->iOnPage) {
            $this->iOnPage = 30;
        }

        $oDefCard = section\Parameters::getByName($this->sectionId(), 'content', 'defCard');
        if ($oDefCard) {
            // раздел с товарами и заданной дефолтной карточкой - выводим товары
            $this->actionList();
        } elseif ($this->searchCard && SysVar::get('catalog.parametricSearch')) {
            // раздел с результатами поиска товаров - выводим настройки
            $this->actionSearchSetting();
        } elseif ($this->collectionField) {
            // раздел с товарами из коллекции - выводим настройки
            $this->actionCollectionSetting();
        } else {
            // не удалось определить тип - новый раздел
            $bParamSearch = SysVar::get('catalog.parametricSearch');
            $bCollectionPage = \Yii::$app->register->moduleExists('Collections', Layer::CATALOG);
            if (!$bParamSearch && !$bCollectionPage) {
                // отключены расширения - сразу выбираем карточку для простого раздела каталога
                $this->actionSetCard();
            } else {
                // форма выбора типа
                $this->actionPageTypeForm();
            }
        }
    }

    /**
     * Список типовых каталожных страниц.
     *
     * @return array
     */
    private function getPageTypes()
    {
        $aSectionTypes = [0 => \Yii::t('catalog', 'section_goods')];

        if (SysVar::get('catalog.parametricSearch')) {
            $aSectionTypes[1] = \Yii::t('catalog', 'section_search');
        }

        if (\Yii::$app->register->moduleExists('Collections', Layer::CATALOG)) {
            $aSectionTypes[2] = \Yii::t('catalog', 'section_collection');
        }

        return $aSectionTypes;
    }

    /**
     * Форма выбора типа страницы.
     */
    protected function actionPageTypeForm()
    {
        $this->setPanelName(\Yii::t('catalog', 'section_type_select'));

        $this->render(new view\PageTypeForm([
            'pageTypes' => $this->getPageTypes(),
        ]));
    }

    /**
     * Обработка события выбора типа раздела.
     */
    protected function actionSetPageType()
    {
        $type = $this->getInDataVal('section_type', false);

        if ($type === false) {
            $this->init();
        } elseif ($type == 1) {
            $this->actionSetSearchCard();
        } elseif ($type == 2) {
            $this->actionSetCollectionField();
        } else {
            $this->actionSetCard();
        }
    }

    /**
     * Форма выбора карточки для поисковой страницы.
     */
    protected function actionSetSearchCard()
    {
        $this->render(new view\SetSearchCard([
            'goodsCardList' => catalog\Card::getGoodsCardList(true),
        ]));
    }

    /**
     * Форма выборка поля карточки для страницы с коллекцией.
     */
    protected function actionSetCollectionField()
    {
        $this->render(new view\SetCollectionField([
            'collectionFields' => catalog\Card::getCollectionFields(),
        ]));
    }

    /**
     * Сохранение параметров модуля, пришедших из форм
     */
    protected function actionSaveConfig()
    {
        $data = $this->get('data');

        if (isset($data['onPage'])) {
            $data['onPage'] = abs((int) $data['onPage']);
        }
        if (isset($data['onPageCollection'])) {
            $data['onPageCollection'] = abs((int) $data['onPageCollection']);
        }

        if (isset($data['related_from'])) {
            catalog\RelatedSections::deleteAll(['target_section' => $this->sectionId()]);

            if ($data['related_from'] !== '') {
                $aSections = explode(',', $data['related_from']);

                foreach ($aSections as $section) {
                    catalog\RelatedSections::addRelation($this->sectionId(), $section);
                }
            }
        }

        // если пришло поле с разделом формы заказа - то раздел должен существовать
        if (isset($data['buyFormSection'])) {
            $oSection = section\Tree::getSection($data['buyFormSection']);
            if (!$oSection) {
                unset($data['buyFormSection']);
            }
        }

        if (isset($data['recentlyViewedOnPage']) && ($data['recentlyViewedOnPage'] > RecentlyViewed\Module::getMaxCountGoodOnPage())) {
            throw new UserException(\Yii::t('catalog', 'error_exceeded_max_value', ['paramName' => \Yii::t('catalog', 'recentlyViewedOnPage'), 'maxValue' => RecentlyViewed\Module::getMaxCountGoodOnPage()]));
        }

        foreach ($data as $field => $value) {
            catalog\Section::setParam($this->sectionId(), $field, $value);
            if (isset($this->{$field})) {
                $this->{$field} = $value;
            }
        }

        // если создали раздел с коллекцией - перестроить индекс коллекций
        if (isset($data['collectionField'])) {
            // пересобрать все поисковые индексы коллекций
            $oEvent = new GetEngineEvent();
            Search::getSearchEngine($oEvent);
            foreach ($oEvent->getList() as $sName => $sClass) {
                /** @var Search $oSearch */
                $oSearch = new $sClass();
                $oSearch->provideName($sName);
                $oSearch->deleteAll();
                $oSearch->restore();
            }

            Service::updateSearchIndex();

            /* Выведем панель сортировки для коллекций по умолчанию */
            section\Parameters::setParams($this->sectionId(), 'content', 'showSort', 1);
            section\Parameters::setParams($this->sectionId(), 'content', 'onPageCollection', 12);
        }

        $this->actionInit();
    }

    /**
     * Настройки для страницы с товарами.
     */
    protected function actionSettings()
    {
        $aData = [
            'buyFormSection' => $this->getParamValue('buyFormSection'),
            'template' => $this->getParamValue('template'),
            'showFilter' => $this->getParamValue('showFilter'),
            'showSort' => $this->getParamValue('showSort'),
            'onPage' => (int) $this->getParamValue('onPage'),
            'relatedTpl' => $this->getParamValue('relatedTpl'),
            'includedTpl' => $this->getParamValue('includedTpl'),
            'related_from' => catalog\RelatedSections::getRelationsByPageId($this->sectionId()),
            'showSubSectionObjects' => $this->getParamValue('showSubSectionObjects'),
        ];

        $bShowRecentlyViewed = (bool) SysVar::get('catalog.goods_recentlyViewed');

        if ($bShowRecentlyViewed) {
            $aData += [
                'recentlyViewedTpl' => $this->getParamValue('recentlyViewedTpl'),
                'recentlyViewedOnPage' => $this->getParamValue('recentlyViewedOnPage'),
            ];
        }

        $this->render(new view\Settings([
            'fieldData' => [
                'listPageTemplates' => CatalogViewer\State\ListPage::getTemplates(),
                'listPageRelatedList' => CatalogViewer\State\ListPage::getRelatedList(),
                'showRecentlyViewed' => $bShowRecentlyViewed,
                'maxCountGoodOnPage' => RecentlyViewed\Module::getMaxCountGoodOnPage(),
                'randomRelated' => SysVar::get('catalog.random_related'),
            ],
            'data' => $aData,
        ]));
    }

    /**
     * Настройка для страницы с поиском товаров.
     */
    protected function actionSearchSetting()
    {
        $this->setPanelName(\Yii::t('catalog', 'filter_editor'));

        $this->render(new view\SearchSetting([
            'listPageTemplates' => CatalogViewer\State\ListPage::getTemplates(),
            'data' => [
                'template' => $this->getParamValue('template'),
                'onPage' => $this->getParamValue('onPage'),
            ],
        ]));
    }

    /**
     * Настройка для страницы с коллекцией товаров.
     */
    protected function actionCollectionSetting()
    {
        $this->setPanelName(\Yii::t('catalog', 'collection_panel_name'));

        $this->render(new view\CollectionSetting([
            'listPageTemplates' => CatalogViewer\State\ListPage::getTemplates(),
            'data' => [
                'template' => $this->getParamValue('template'),
                'onPage' => $this->getParamValue('onPage'),
                'onPageCollection' => $this->getParamValue('onPageCollection'),
                'showSort' => $this->getParamValue('showSort'),
                'showFilter' => $this->getParamValue('showFilter'),
            ],
        ]));
    }

    /**
     * Получение значения параметра для раздела.
     *
     * @param string $sFieldName имя параметра
     *
     * @return string
     */
    private function getParamValue($sFieldName)
    {
        $sVal = section\Parameters::getValByName($this->sectionId(), 'content', $sFieldName, true);

        // Получение языкового параметра
        if (!$sVal and ($sFieldName == 'buyFormSection')) {
            $sVal = section\Parameters::getValByName($this->sectionId(), catalog\Api::LANG_GROUP_NAME, $sFieldName, true, true);
        }

        return $sVal ? $sVal : '';
    }
}
