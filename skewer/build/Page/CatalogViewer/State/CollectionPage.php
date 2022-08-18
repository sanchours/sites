<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\SysVar;
use skewer\build\Catalog\Collections\SeoElementCollection;
use skewer\build\Design\Zones;
use skewer\components\catalog;
use skewer\components\ecommerce;
use skewer\components\seo;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Объект вывода списка товарных позиций для старницы бренда
 * Class CollectionPage.
 */
class CollectionPage extends ListPage
{
    /** @var int Идентификатор позиции в коллекции */
    protected $collection = 0;

    /** @var bool Имя карточки коллекции */
    private $card = false;

    /** @var bool Имя поля в карточке товара */
    private $field = false;

    /** @var array */
    private $obj = [];

    /** @var string Шаблон вывода */
    protected $sMainTpl;

    /** @var string шалон пустого результата фильтра */
    private $sNotFoundFiltTemplate = 'NotFound.twig';

    /**
     * Инициализация объекта коллекции по идентификатору.
     *
     * @param int|string $collection id or alias
     *
     * @throws NotFoundHttpException
     *
     * @return bool
     */
    public function setCollectionId($collection)
    {
        list($this->card, $this->field) = explode(':', $this->getModuleField('collectionField'));

        $this->obj = catalog\ObjectSelector::getElementCollection($collection, $this->card, $this->sectionId());

        if (!$this->obj) {
            throw new NotFoundHttpException('Collection not found');
        }
        if (!$this->obj['active']) {
            $bRedirect = SysVar::get('catalog.redirect_hide_collection');
            if ($bRedirect) {
                $sRedirectUrl = Tree::getSectionAliasPath($this->sectionId());
                \Yii::$app->getResponse()->redirect($sRedirectUrl, '302')->send();
            } else {
                throw new NotFoundHttpException('Не найден элемент коллекции');
            }
        }

        \Yii::$app->router->setLastModifiedDate($this->obj['last_modified_date']);

        $this->collection = ArrayHelper::getValue($this->obj, 'id', 0);

        return true;
    }

    /**
     * Получение списка товарных позиций для текущей страницы.
     *
     * @return bool
     */
    protected function getGoods()
    {
        if (!$this->iCount) {
            return false;
        }

        /*
         * Запрашиваем GoodSelector
         * Применяем дефолтную сортировку если не задано поле сортировки из интерфейса
         */
        $oSelector = catalog\GoodsSelector::getList4Collection($this->card, $this->field, $this->collection, !$this->sSortField);

        if (!$oSelector) {
            return false;
        }

        if ($this->getModuleField('showFilter')) {
            $this->applyFilter($oSelector);
        }

        $this->list = $oSelector
            ->onlyVisibleSections()
            ->condition('active', 1)
            ->sort($this->sSortField, ($this->sSortWay == 'down' ? 'DESC' : 'ASC'), false)
            ->limit($this->iCount, $this->iPageId, $this->iAllCount)
            ->withSeo($this->getSection())
            ->parse();

        if (catalog\Api::isIECommerce()) {
            $this->list = ecommerce\Api::addEcommerceDataInGoodsCollection($this->list, $this->sectionId(), $this->obj['title'], 'Основной список');
        }

        foreach ($this->list as &$item) {
            $item['show_detail'] = !catalog\Card::isDetailHiddenByCard($item['card']);
        }

        return true;
    }

    /**
     * Вернёт объект элемента коллекции.
     *
     * @return array
     */
    public function getObjElementCollection()
    {
        return $this->obj;
    }

    public function build()
    {
        $this->oModule->setStatePage(Zones\Api::DETAIL_LAYOUT);

        if (!$this->obj) {
            return false;
        }

        $this->setSeo();

        site\Page::setTitle($this->obj['title']);

        site\Page::setAddPathItem($this->obj['title'], $this->buildUrlCollection());

        $this->getModule()->setData('useMainSection', 1);

        if (empty($this->list)) {
            if ($this->bFilterUsed) {
                $this->getModule()->setTemplate($this->sNotFoundFiltTemplate);

                return;
            }
        }

        parent::build();

        //$this->showNearItems();

        $this->getModule()->setData('collection', $this->obj);
        $this->getModule()->setData('view', $this->sTpl);

        // шаблон
        $this->sMainTpl = $this->getModuleField('templateCollectionPage');
        $this->getModule()->setTemplate($this->sMainTpl);

        return true;
    }

    /**
     * Установит seo-данные.
     */
    public function setSeo()
    {
        if (is_numeric($this->card)) {
            $iCardId = $this->card;
            $sCardName = catalog\Card::getName($this->card);
        } else {
            $sCardName = $this->card;
            $oCollection = catalog\Collection::getCollection($sCardName);
            $iCardId = $oCollection->getVal('id');
        }

        $oSeo = new SeoElementCollection(0, $iCardId, $this->obj, $sCardName);

        $this->oModule->setEnvParam(seo\Api::SEO_COMPONENT, $oSeo);
        $this->oModule->setEnvParam(seo\Api::OPENGRAPH, '');
        site\Page::reloadSEO();
    }

    /**
     * Установка rel=canonical.
     */
    public function setCanonical()
    {
        $sCanonical = site\Site::httpDomain() . $this->buildUrlCollection();
        $oPage = site\Page::getRootModule();
        $oPage->setData('canonical_pagination', $sCanonical);
    }

    /**
     * Построит урл элемента коллекции.
     *
     * @return string
     */
    public function buildUrlCollection()
    {
        return catalog\Parser::buildUrl($this->sectionId(), ArrayHelper::getValue($this->obj, 'id'), ArrayHelper::getValue($this->obj, 'alias'));
    }

    /**
     * Вывод PathLine.
     */
    protected function setPathLine()
    {
        $aURLParams = $_GET;
        unset($aURLParams['page'] , $aURLParams['url']);

        $aURLParams['goods-alias'] = ArrayHelper::getValue($this->obj, 'alias', 0);

        foreach ($aURLParams as $sKey => $mParam) {
            if (is_array($mParam)) {
                foreach ($mParam as $sWKey => $mValue) {
                    $aURLParams[$sKey . '[' . $sWKey . ']'] = $mValue;
                }

                unset($aURLParams[$sKey]);
            }
        }

        $this->setCanonical();

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

    protected function showNearItems()
    {
        $aData = [
            'section' => $this->sectionId(),
            'next' => catalog\ObjectSelector::getNext($this->collection, $this->card),
            'prev' => catalog\ObjectSelector::getPrev($this->collection, $this->card),
        ];

        $this->oModule->setData('nearItems', $aData);
    }
}
