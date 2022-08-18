<?php

namespace skewer\build\Page\CatalogViewer;

use skewer\base\router\Router;
use skewer\base\section\Parameters;
use skewer\base\site_module;
use skewer\build\Tool\Review\Api as ReviewsApi;
use skewer\components\catalog;
use skewer\components\design\DesignManager;
use skewer\components\rating\Rating;

/**
 * Модуль вывода каталога в пользовательской части.
 */
class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    /** @var string Шаблон для вывода списка */
    public $template = '';

    /** @var string Шаблон детальной товара или элемента коллекции */
    public $template_detail = '';

    /** @var string раздел формы заказа. Если значение <0 то параметр будет браться из глобальной языковой метки catalog */
    public $buyFormSection = -1;

    /** @var string Имя поля для сотрировки списка */
    public $listSortField = '';

    /** @var string Флаг вывода отзывов */
    public $showReviews = false;

    /** @var int Кол-во позиций на страницу */
    public $onPage = 3;

    /** @var int Кол-во коллекций на страницу */
    public $onPageCollection = 12;

    /** @var bool Вывод на главной */
    public $onMain = false;

    /** @var bool Вывод коллекции на главной */
    public $onMainCollection = false;

    /** @var bool Вывод поисковой страницы */
    public $searchCard = false;

    /** @var bool Вывод страницы коллекции */
    public $collectionField = false;

    /** @var bool Показывать панель фильтра для списка */
    public $showFilter = false;

    /** @var bool Показывать плашку сортировки в списке */
    public $showSort = false;

    /** @var State\DetailPage|State\ListOnMain|State\ListPage Состояние вывода каталога */
    private $oState;

    /** @var string Заголовок страницы на главной */
    public $titleOnMain = '';

    /** @var string Шаблон для вывода списка связанных товаров в детальной */
    public $relatedTpl = '';

    /** @var string Шаблон для вывода списка товаров из комплекта в детальной */
    public $includedTpl = '';

    /** @var int Идентификатор раздела с коллекциями для вывода на главной */
    public $onMainCollectionSection = 0;

    /** @var int Количество выводимых ранее просмотренных товаров */
    public $recentlyViewedOnPage = 5;

    /** @var string Шаблон блока "Ранее просмотр. товары" */
    public $recentlyViewedTpl = '';

    /** @var bool Показывать товары потомков */
    public $showSubSectionObjects = 0;

    /** @var string Тип вывода отзывов в табах */
    public $reviewsTemplate = ReviewsApi::TYPE_SHOW_LIST;

    /** @var string Шаблон списка коллекций(CollectionList) */
    public $templateCollectionList = 'list';

    /** @var string Шаблон детальной коллекции(CollectionPage) */
    public $templateCollectionPage = 'CollectionPage.twig';

    /**
     * Инициализация модуля.
     *
     * @return bool
     */
    public function init()
    {
        // Если параметр не задан строго для раздела, то взять языковую настройку парамера buyFormSection
        if ($this->buyFormSection < 0) {
            if ($oParamFormSection = Parameters::getByName($this->sectionId(), catalog\Api::LANG_GROUP_NAME, 'buyFormSection', true, true)) {
                $this->buyFormSection = $oParamFormSection->value;
            } else {
                $this->buyFormSection = '';
            }
        }

        $sGoodsAlias = urldecode($this->getStr('goods-alias', ''));

        $iGoodsId = $this->getInt('item', 0);

        if ($this->getModuleField('onMain')) {
            $this->oState = new State\ListOnMain($this);
        } elseif ($this->getModuleField('onMainCollection')) {
            $this->oState = new State\CollectionOnMain($this);
        } elseif ($this->getModuleField('searchCard')) {
            $this->oState = new State\SearchPage($this);
        } elseif ($this->getModuleField('collectionField')) {
            if ($iGoodsId || $sGoodsAlias) {
                $this->oState = new State\CollectionPage($this);
                $this->oState->setCollectionId($sGoodsAlias ? $sGoodsAlias : $iGoodsId);
            } else {
                $this->oState = new State\CollectionList($this);
            }
        } elseif ($iGoodsId || $sGoodsAlias) {
            $this->oState = new State\DetailPage($this);
            $this->oState->findGoods($iGoodsId, $sGoodsAlias);
        } else {
            $this->oState = new State\ListPage($this);
        }

        /*Флаг добавления теней для заголовков*/
        $this->setData('show_shadows', DesignManager::getParamValue('modules.catalogbox.boxshadow.boxshadow_enable'));

        if ($this->sectionId() != \Yii::$app->sections->main()) {
            $this->setData('show_detail', !catalog\Card::isDetailHidden($this->sectionId()));
        }

        return true;
    }

    public function execute()
    {
        if ($this->executeRequestCmd()) {
            return psComplete;
        }

        if ($this->oState->showFilter()) {
            // Откладываем запуск если не отработал модуль фильтра
            $oFilterModule = $this->getProcess('out.CatalogFilter', psAll);
            if (($oFilterModule instanceof site_module\Process) && !$oFilterModule->isComplete()) {
                return psWait;
            }
        } else {
            // удаляем процесс из вывода
            $oFilterModule = $this->getProcess('out.CatalogFilter', psAll);
            if ($oFilterModule instanceof site_module\Process) {
                $oFilterModule->setStatus(psBreak);
            }
        }

        $this->oState->show();

        return psComplete;
    }

    /** Осуществление ajax-голосования */
    protected function cmdAddRating()
    {
        $this->setParser(parserJSONAjax);

        $iObjectId = (int) $this->get('objId');
        $iRating = abs((int) $this->get('rating'));
        if ($iRating > 5) {
            $iRating = 5;
        }

        if (!$iObjectId or !$iRating) {
            return;
        }

        $oRating = new Rating($this->getModuleName());
        if ($sAnswer = $oRating->addRate($iObjectId, $iRating)) {
            $this->setData('Rating', $oRating->parse($iObjectId, true));
            $this->setData('Answer', $sAnswer);
        }
    }

    /**
     * Метод для быстрого просмотра товара ajax.
     */
    public function cmdQuickView()
    {
        if (!Api::checkQuickView()) {
            return [];
        }

        $iGoodsId = $this->getInt('item', 0);
        $this->oState = new State\QuickView($this);
        $this->oState->findGoods($iGoodsId);

        $this->oState->show();

        $html = site_module\Parser::render($this->oContext);
        $html = Router::rewriteURLs($html);

        echo json_encode(['html' => $html]);
        exit;
    }

    /**
     * Получить объект состояния вывода каталога.
     *
     * @return State\DetailPage|State\ListOnMain|State\ListPage
     */
    public function getStateObject()
    {
        return $this->oState;
    }
}
