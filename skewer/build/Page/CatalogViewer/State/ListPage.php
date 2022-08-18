<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\site_module\Process;
use skewer\base\SysVar;
use skewer\build\Page\CatalogViewer\Api;
use skewer\components\catalog;
use skewer\components\ecommerce;
use skewer\components\filters\IndexedFilter;
use skewer\components\seo\SeoWrapper4Filter;
use skewer\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Объект вывода списка товарных позиций
 * Class ListPage.
 */
class ListPage extends Prototype
{
    /** @var array Набор товарных позиций для вывода */
    protected $list = [];

    /** @var int Всего найдено товарных позиций */
    protected $iAllCount = 0;

    /** @var int Номер страницы */
    protected $iPageId = 1;

    /** @var int Кол-во позиций на страницу */
    protected $iCount = 12;

    /** @var string Шаблон для вывода */
    protected $sTpl = '';

    /** @var string Поле для сортировки */
    protected $sSortField = '';

    /** @var string Напровление сортировки */
    protected $sSortWay = 'up';

    /** @var string шаблон пустого результата фильтра */
    private $sNotFoundFiltTemplate = 'NotFound.twig';

    /** @var string шаблон пустого списка товаров */
    private $sEmptyListTemplate = 'Empty.twig';

    /** @var array Набор шаблонов для каталога */
    public static $aTemplates = [
        'list' => [
            'title' => 'Editor.type_catalog_list',
            'file' => 'SimpleList.twig',
        ],

        'gallery' => [
            'title' => 'Editor.type_catalog_gallery',
            'file' => 'GalleryList.twig',
        ],

        'table' => [
            'title' => 'Editor.type_catalog_table',
            'file' => 'TableList.twig',
        ],
    ];

    /** @var bool Факт использования фильтрации */
    protected $bFilterUsed = false;

    /**
     * Набор шаблонов для вывода в пользовательской части.
     *
     * @return array
     */
    public static function getTemplates()
    {
        $aList = ['list', 'gallery', 'table'];

        $aOut = [];
        foreach ($aList as $sName) {
            $aOut[$sName] = \Yii::t('catalog', 'tpl_' . $sName);
        }

        return $aOut;
    }

    /**
     * Отдает список всех каталожных разделов.
     *
     * @return array
     */
    public static function getRelatedList()
    {
        $aSections = Template::getSectionsByTplId(Template::getCatalogTemplate());
        $aList = ArrayHelper::map($aSections, 'id', 'title');
        asort($aList, SORT_STRING);

        return $aList;
    }

    public function init()
    {
        \Yii::$app->router->setLastModifiedDate(catalog\model\GoodsTable::getMaxLastModifyDate());

        // постраничный
        $this->iPageId = $this->getModule()->getInt('page', 1);
        $this->iCount = $this->getModuleField('onPage', $this->iCount);

        // сортировка
        $this->sSortField = $this->getModuleField('listSortField');
        if ($sSortField = $this->getModule()->get('sort')) {
            $this->sSortField = $sSortField;
        }

        if ($sSortWay = $this->getModule()->get('way')) {
            $this->sSortWay = ($sSortWay == 'down' ? 'down' : 'up');
        }

        // выбор шаблона для вывода
        $this->getTpl();

        // получение набора товаров
        $this->getGoods();
    }

    public function build()
    {
        if ($this->bFilterUsed) {
            $this->setFilterSeoData();
        }

        if (empty($this->list)) {
            if ($this->bFilterUsed) {
                $this->getModule()->setTemplate($this->sNotFoundFiltTemplate);

                return;
            }

            $oPage = site\Page::getRootModule();
            $aStaticContent = $oPage->getData('staticContent');
            $sText = (is_array($aStaticContent) and isset($aStaticContent['text'])) ? $aStaticContent['text'] : '';
            if (Html::hasContent($sText) || !SysVar::get('catalog.section_filling', 0)) {
                return;
            }

            $this->getModule()->setTemplate($this->sEmptyListTemplate);

            return;
        }

        // Рейтинг
        $this->addRating($this->list);

        // парсинг
        $this->getModule()->setData('section', $this->getModule()->getEnvParam('sectionId'));
        $this->getModule()->setData('aObjectList', $this->list);
        $this->getModule()->setData('cartSectionId', \Yii::$app->sections->getValue('cart'));
        $this->getModule()->setData('form_section', $this->getModuleField('buyFormSection'));
        $this->getModule()->setData('useCart', site\Type::isShop());
        $this->getModule()->setData('hideBuy1lvlGoods', SysVar::get('catalog.hideBuy1lvlGoods'));
        $this->getModule()->setData('quickView', Api::checkQuickView());

        // панель сортировки для списка
        if ($this->getModuleField('showSort') and $this->list) {
            $this->showSortPanel();
        }

        // шаблон
        $this->getModule()->setTemplate(self::$aTemplates[$this->sTpl]['file']);

        // постраничник
        $this->setPathLine();
    }

    /**
     * Получение списка товарных позиций для текущей страницы.
     *
     * @return bool
     */
    protected function getGoods()
    {
        if ($this->getModuleField('showSubSectionObjects')) { // Вывод товаров из разделов потомков
            $section = Tree::getAllSubsection($this->sectionId(), true, true);
            $oSelector = catalog\GoodsSelector::getList4Section($section);

            // для построения нормального урла у товаров из разедлов-потомков
            $this->getModule()->setData('useMainSection', 1);
        } else { // обычный список
            $oSelector = catalog\GoodsSelector::getList4Section($this->getSection());
        }

        if ($this->getModuleField('showFilter')) {
            $this->applyFilter($oSelector);
        }

        if (!$this->iCount) {
            return false;
        }

        $this->list = $oSelector
            ->condition('active', 1)
            ->sort($this->sSortField, ($this->sSortWay == 'down' ? 'DESC' : 'ASC'))
            ->limit($this->iCount, $this->iPageId, $this->iAllCount)
            ->withSeo($this->getSection())
            ->parse();
        if (catalog\Api::isIECommerce()) {
            $this->list = ecommerce\Api::addEcommerceDataInGoods($this->list, $this->sectionId(), 'Основной список');
        }

        return true;
    }

    /**
     * Вывод плашки для выбора сотрировки и типа отображения списка.
     */
    protected function showSortPanel()
    {
        $oGoodsSelector = new catalog\GoodsSelector();
        $oGoodsSelector->selectCard(catalog\GoodsSelector::card4Section($this->sectionId()));
        $aFieldList = $oGoodsSelector->getCardFields();

        $aSortFieldArr = [];
        foreach ($aFieldList as $oField) {
            if (!$oField->getAttr('show_in_sortpanel') ||
                (!$oField->getAttr('show_in_list') && $oField->getAttr('show_in_sortpanel'))) {
                continue;
            }

            $aSortFieldArr[] = [
                'name' => $oField->getName(),
                'title' => $oField->getTitle(),
                'sel' => ($this->sSortField == $oField->getName()) ? $this->sSortWay : false,
            ];
        }
        if (!empty($aSortFieldArr)) {
            $this->getModule()->setData('filter', ['aFields' => $aSortFieldArr]);
            $this->getModule()->setData('viewState', $this->sTpl);
            $this->getModule()->setData('sortState', $this->sSortField);
            $this->getModule()->setData('defSortState', $this->getModuleField('listSortField'));
            $this->getModule()->setData('sortWay', $this->sSortWay);
        }
    }

    /**
     * Установка шаблона для вывода.
     */
    private function getTpl()
    {
        // берем из параметров раздела
        $this->sTpl = $this->getModuleField('template');

        // проверяем перекрытие из GET
        if ($sView = $this->getModule()->get('view')) {
            $this->sTpl = $sView;
        }

        // убеждаемся в наличии
        if (!isset(self::$aTemplates[$this->sTpl])) {
            $this->sTpl = 'list';
        }
    }

    /**
     * Вывод PathLine.
     */
    protected function setPathLine()
    {
        $aURLParams = $_GET;
        unset($aURLParams['page'] , $aURLParams['url']);

        if ($this->getModule()->getStr('condition')) {
            $aURLParams['(filtercond)condition'] = $this->getModule()->getStr('condition');
        }

        foreach ($aURLParams as $sKey => $mParam) {
            if (is_array($mParam)) {
                foreach ($mParam as $sWKey => $mValue) {
                    $aURLParams[$sKey . '[' . $sWKey . ']'] = $mValue;
                }

                unset($aURLParams[$sKey]);
            }
        }

        catalog\Api::removeTextContent($this->iPageId);
        $this->getModule()->getPageLine(
            $this->iPageId,
            $this->iAllCount,
            $this->sectionId(),
            $aURLParams,
            ['onPage' => $this->iCount],
            'aPages',
            !$this->getModule()->isMainModule()
        );
    }

    /** Установаить seo-данные фильтра */
    private function setFilterSeoData()
    {
        $oFilterProcess = \Yii::$app->processList->getProcess('out.CatalogFilter', psAll);

        if ($oFilterProcess instanceof Process) {
            /** @var \skewer\build\Page\CatalogFilter\Module $oFilterModule */
            $oFilterModule = $oFilterProcess->getModule();

            $oFilter = $oFilterModule->getFilter();

            if ($oFilter instanceof IndexedFilter) {
                $oSeoWrapperFilter = new SeoWrapper4Filter($oFilter, $this->sectionId());

                site\Page::setTitle($oSeoWrapperFilter->generateH1());

                site\Page::setMetaTags([
                    'SEOTitle' => $oSeoWrapperFilter->generateMetaTitle(),
                    'SEODescription' => $oSeoWrapperFilter->generateMetaDescription(),
                    'SEOKeywords' => $oSeoWrapperFilter->generateMetaKeywords(),
                ]);

                site\Page::setStaticContent($oSeoWrapperFilter->generateStaticContent());

                $aBreadCrumbsItems = $oSeoWrapperFilter->getBreadcrumbsItems();
                foreach ($aBreadCrumbsItems as $item) {
                    site\Page::setAddPathItemData($item);
                }

                // Перекрываем rel=Canonical, установленный пагинатором
                $oRootModule = site\Page::getRootModule();
                $sCanonicalUrl = $oSeoWrapperFilter->buildCanonicalUrl();
                $oRootModule->setData('canonical_pagination', $sCanonicalUrl);
            }
        }
    }

    protected function applyFilter(catalog\GoodsSelector $oSelector)
    {
        $oFilterProcess = \Yii::$app->processList->getProcess('out.CatalogFilter', psAll);

        if ($oFilterProcess instanceof Process) {
            /** @var \skewer\build\Page\CatalogFilter\Module $oFilterModule */
            $oFilterModule = $oFilterProcess->getModule();

            $this->bFilterUsed = $oSelector->applyFilter($oFilterModule->getFilter());
        }
    }
}
