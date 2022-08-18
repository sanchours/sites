<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\SysVar;
use skewer\build\Adm\GuestBook\models;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Catalog\Goods\SeoGoodModifications;
use skewer\build\Design\Zones;
use skewer\build\Page\CatalogViewer;
use skewer\build\Page\GuestBook;
use skewer\build\Page\RecentlyViewed;
use skewer\build\Page\WishList\WishList;
use skewer\build\Tool\Review\Api as ReviewsApi;
use skewer\components\catalog;
use skewer\components\ecommerce;
use skewer\components\GalleryOnPage\Api as GalOnPageApi;
use skewer\components\seo\Api;
use skewer\components\traits\CanonicalOnPageTrait;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class DetailPage extends Prototype
{
    use CanonicalOnPageTrait;

    /** Метка процесса "Отзывы в товарах" */
    const LABEL_GOODSREVIEWS = 'GoodsReviewsModule';

    /** @var array Данные товарной позиции */
    private $aGood = [];

    /** @var string Шаблон вывода */
    private $sTpl;

    /** @var string Псевдоним текущего товара */
    private $sGoodsAlias = '';

    /** @var int ID текущего товара */
    private $iGoodsId = 0;

    protected $bShowFilter = false;

    public function init()
    {
        /*Если установлено скрытие детальных, 404*/
        if (catalog\Card::isDetailHidden($this->sectionId())) {
            throw new NotFoundHttpException();
        }
        $this->oModule->setStatePage(Zones\Api::DETAIL_LAYOUT);

        $sBaseCardName = catalog\Card::DEF_BASE_CARD;

        $this->aGood = catalog\GoodsSelector::get($this->sGoodsAlias ? $this->sGoodsAlias : $this->iGoodsId, $sBaseCardName, false, $this->sectionId());

        \Yii::$app->router->setLastModifiedDate($this->aGood['last_modified_date']);

        // Не отдавать модификацию товара, если выключен показ модификаций
        if (!SysVar::get('catalog.goods_modifications') and ($this->aGood['id'] != $this->aGood['main_obj_id'])) {
            $this->aGood = null;
        }

        if (empty($this->aGood)) {
            throw new NotFoundHttpException('Не найдена товарная позиция');
        }
        if (!$this->aGood['active']) {
            $bRedirect = SysVar::get('catalog.redirect_hide_good');
            if ($bRedirect) {
                $sRedirectUrl = Tree::getSectionAliasPath($this->aGood['main_section']);
                \Yii::$app->getResponse()->redirect($sRedirectUrl, '302')->send();
            } else {
                throw new NotFoundHttpException('Не найдена товарная позиция');
            }
        }
        if (catalog\Api::isIECommerce()) {
            $aGoods = ecommerce\Api::addEcommerceDataInGoods([$this->aGood], $this->sectionId(), 'Детальная страница');
            $this->aGood = reset($aGoods);
        }

        // проверка на раздел
        $aSectionList = catalog\Section::getList4Goods($this->aGood['main_obj_id'], $this->aGood['base_card_id']);
        if ($aSectionList == [] || !in_array($this->getSection(), $aSectionList)) {
            throw new NotFoundHttpException('Не найдена товарная позиция');
        }
        CatalogViewer\AssetDetail::register(\Yii::$app->view);

        $this->sTpl = $this->getModuleField('template_detail') ?: 'SimpleDetail.twig';
    }

    public function build()
    {
        $iObjectId = $this->aGood['id'];
        $this->oModule->setEnvParam('iCatalogObjectId', $iObjectId);

        $iMainObjId = (isset($this->aGood['main_obj_id']) and $this->aGood['main_obj_id']) ? $this->aGood['main_obj_id'] : $iObjectId;

        /*Если это нормальный товар и не модификация, попробуем добавить "ближайшие товары" */
        if ($iMainObjId == $iObjectId) {
            $this->showNearItems($iObjectId);
        }

        // товары аналоги
        if (SysVar::get('catalog.goods_modifications')) {
            $this->showModificationsItems($iMainObjId, $iObjectId);
        }

        // Ранее просмотренные товары
        $this->showRecentlyViewed();

        // товары идущие в комплекте товаров
        if (SysVar::get('catalog.goods_include')) {
            $this->showIncludedItems($iObjectId);
        }

        // модуль сопутствующих товаров
        if (SysVar::get('catalog.goods_related')) {
            $this->showRelatedItems($iObjectId);
        }

        // Показать рейтинг. Разрешить в нём голосование, если отключены отзывы к товару. Иначе голосование осуществляется через отзывы
        $this->addRating($this->aGood, $this->showingReviews());

        // отдаем данные на парсинг
        $this->oModule->setData('aObject', $this->aGood);
        $this->oModule->setData('cartSectionId', \Yii::$app->sections->getValue('cart'));
        $this->oModule->setData('aTabs', $this->buildTabs());
        // Вывод микроразметки отзывов
        if ($this->showingReviews()) {
            $this->oModule->setData('reviews', $this->getMicroDataReview());
        }
        $this->oModule->setData('form_section', $this->getModuleField('buyFormSection'));
        $this->oModule->setData('section', $this->getModule()->getEnvParam('sectionId'));
        $this->oModule->setData('useCart', site\Type::isShop());
        $this->oModule->setData('isMainObject', $iMainObjId == $iObjectId);
        $this->oModule->setData('hide2lvlGoodsLinks', SysVar::get('catalog.hide2lvlGoodsLinks'));
        $this->oModule->setData('hideBuy1lvlGoods', SysVar::get('catalog.hideBuy1lvlGoods'));
        $this->oModule->setData('currency_type', SysVar::get('catalog.currency_type'));
        $this->oModule->setData('wishlist', WishList::isModuleOn());
        $this->oModule->setData('quickView', CatalogViewer\Api::checkQuickView());
        $this->oModule->setData('condition_print_param', catalog\Attr::SHOW_IN_PARAMS);
        $this->oModule->setTemplate($this->sTpl);

        $this->setSeo($this->aGood);
        site\Page::setTitle($this->aGood['title']);
        site\Page::setAddPathItem($this->aGood['title'], $this->aGood['url']);
    }

    /**
     * Поиск товара по идентификаторам
     *
     * @param int $iGoodsId
     * @param string $sGoodsAlias
     *
     * @return bool
     */
    public function findGoods($iGoodsId, $sGoodsAlias = '')
    {
        $this->iGoodsId = $iGoodsId;
        $this->sGoodsAlias = $sGoodsAlias;

        return true;
    }

    public function setSeo($aGood)
    {
        if ($aGood['main_obj_id'] != $aGood['id']) {
            $oSeo = new SeoGoodModifications(0, $this->oModule->sectionId(), $aGood);
        } elseif (!empty($aGood['card'])) {
            $oSeo = new SeoGood(0, $this->oModule->sectionId(), $aGood);
            $oSeo->setExtraAlias($aGood['card']);
        } else {
            $oSeo = null;
        }

        $this->oModule->setEnvParam(Api::SEO_COMPONENT, $oSeo);

        if (isset($aGood['canonical_url'])) {
            $this->setCanonical($aGood['canonical_url']);
        }

        $oSeo->initSeoData();
        /* Вывод description в шаблон(для микроразметки) */
        $this->oModule->setData('seo_description', ($oSeo->description) ? $oSeo->description : $oSeo->parseField('description', ['sectionId' => $oSeo->getSectionId()]));

        $this->oModule->setEnvParam(Api::OPENGRAPH, $this->oModule->renderTemplate('OpenGraph.php', [
            'aGood' => $aGood,
            'oSeoComponent' => $oSeo,
        ]));

        site\Page::reloadSEO();
    }

    /**
     * Выводит модуль перехода на ближайшие элементы.
     *
     * @param $iObjectId
     */
    protected function showNearItems($iObjectId)
    {
        $aData = [
            'section' => $this->sectionId(),
            'next' => catalog\GoodsSelector::getNext($iObjectId, $this->sectionId()),
            'prev' => catalog\GoodsSelector::getPrev($iObjectId, $this->sectionId()),
        ];

        \Yii::$app->router->setLastModifiedDate(catalog\model\GoodsTable::getMaxLastModifyDate());

        if ($iObjectId != $aData['next']['id'] && $iObjectId != $aData['prev']['id']) {
            $this->oModule->setData('nearItems', $aData);
        }
    }

    /**
     * Вывод модуля сопутствующих товаров.
     *
     * @param $iObjectId
     */
    private function showRelatedItems($iObjectId)
    {
        $sTpl = $this->getModuleField('relatedTpl');
        if (!in_array($sTpl, ['list', 'gallery', 'table'])) {
            $sTpl = 'gallery';
        }

        $aObjectList = catalog\GoodsSelector::getRelatedList($iObjectId)
            ->onlyVisibleSections()
            ->condition('active', 1)
            ->withSeo($this->getSection())
            ->parse();

        \Yii::$app->router->setLastModifiedDate(catalog\model\GoodsTable::getMaxLastModifyDate());
        if ((empty($aObjectList) && SysVar::get('catalog.random_related')) || (SysVar::get('catalog.random_related'))) {
            //Запрашиваем лимит на случайные сопутствующие товары
            $iNeedCount = SysVar::get('catalog.random_related_count');
            if ($iNeedCount > count($aObjectList)) {
                $iNeedCount = $iNeedCount - count($aObjectList);
                $aBanIds = ArrayHelper::getColumn($aObjectList, 'id');

                $aRandObjectList = catalog\GoodsSelector::getRelatedList4ObjectRand($iObjectId, $this->sectionId(), $iNeedCount, $aBanIds);

                if ($aRandObjectList) {
                    $aRandObjectList = $aRandObjectList->condition('active', 1)
                        ->withSeo($this->getSection())
                        ->parse();
                }

                $aObjectList = array_merge($aObjectList, $aRandObjectList);
            }
        }

        if (count($aObjectList)) {
            if (catalog\Api::isIECommerce()) {
                $aObjectList = ecommerce\Api::addEcommerceDataInGoods($aObjectList, $this->sectionId(), \Yii::t('catalog', 'related_product'));
            }

            foreach ($aObjectList as &$aObject) {
                $aObject['show_detail'] = (int) !catalog\Card::isDetailHiddenByCard($aObject['card']);
            }

            $aData['section'] = $this->sectionId();

            // Рейтинг
            $this->addRating($aObjectList);

            $this->oModule->setData('relatedTpl', $sTpl);
            $this->oModule->setData('relatedItems', $aData);
            $this->oModule->setData('aRelObjList', $aObjectList);

            $this->oModule->setData('gallerySettings_related', htmlentities(GalOnPageApi::getSettingsByEntity('Related', true), ENT_QUOTES, 'UTF-8'));
        }
    }

    /**
     * Вывод товаров из комплекта.
     *
     * @param $iObjectId
     */
    private function showIncludedItems($iObjectId)
    {
        $sTpl = $this->getModuleField('includedTpl');
        if (!in_array($sTpl, ['list', 'gallery', 'table'])) {
            $sTpl = 'gallery';
        }

        $aObjectList = catalog\GoodsSelector::getIncludedList($iObjectId)
            ->onlyVisibleSections()
            ->condition('active', 1)
            ->withSeo($this->getSection())
            ->parse();

        \Yii::$app->router->setLastModifiedDate(catalog\model\GoodsTable::getMaxLastModifyDate());

        if (count($aObjectList)) {
            if (catalog\Api::isIECommerce()) {
                $aObjectList = ecommerce\Api::addEcommerceDataInGoods($aObjectList, $this->sectionId(), \Yii::t('catalog', 'included_product'));
            }

            foreach ($aObjectList as &$aObject) {
                $aObject['show_detail'] = (int) !catalog\Card::isDetailHiddenByCard($aObject['card']);
            }

            $aData['section'] = $this->sectionId();

            // Рейтинг
            $this->addRating($aObjectList);
            $this->oModule->setData('gallerySettings_included', htmlentities(GalOnPageApi::getSettingsByEntity('Included', true), ENT_QUOTES, 'UTF-8'));
            $this->oModule->setData('includedTpl', $sTpl);
            $this->oModule->setData('includedItems', $aData);
            $this->oModule->setData('aIncObjList', $aObjectList);
        }
    }

    /**
     * Вывод товаров аналогов.
     *
     * @param int $iObjectId Ид текущего товара
     * @param int $iExcludeId Id Объекта для исключения из списка модификаций. По умолчанию = id родительского объекта
     */
    private function showModificationsItems($iObjectId, $iExcludeId = 0)
    {
        $aObjectList = catalog\GoodsSelector::getModificationList($iObjectId, $iExcludeId)
            ->condition('active', 1)
            ->withSeo($this->getSection())
            ->parse();

        \Yii::$app->router->setLastModifiedDate(catalog\model\GoodsTable::getMaxLastModifyDate());

        if (count($aObjectList)) {
            if (catalog\Api::isIECommerce()) {
                $aObjectList = ecommerce\Api::addEcommerceDataInGoods($aObjectList, $this->sectionId(), 'Список модификаций');
            }

            $aData['section'] = $this->sectionId();

            // Рейтинг
            $this->addRating($aObjectList);

            $this->oModule->setData('ModificationsItems', $aData);
            $this->oModule->setData('aAngObjList', $aObjectList);
        }
    }

    /**
     * Вернет микроразметку отзывов к выбранному товару.
     *
     * @return string
     */
    private function getMicroDataReview()
    {
        $sHtml = $this->oModule->createAndExecuteProcess('GoodsReviewsModule', GuestBook\Module::className(), [
            'className' => models\GuestBook::GoodReviews,
            'objectId' => $this->aGood['id'],
            'bOnlyMicrodata' => true,
            'typeShow' => ReviewsApi::TYPE_SHOW_LIST,
        ]);

        return $sHtml;
    }

    /**
     * Вывод отзывов к товару.
     *
     * @return array
     */
    private function showReviews()
    {
        $bHideForm = (bool) SysVar::get('catalog.hide_review_form');

        $outText = $this->oModule->createAndExecuteProcess('GoodsReviewsModule', GuestBook\Module::className(), [
            'onPageContent' => GuestBook\Module::onPageGoodsReviews,
            'className' => models\GuestBook::GoodReviews,
            'objectId' => $this->aGood['id'],
            'actionForm' => '#tabs-reviews',
            // Отображать рейтинг в отзывах?
            'rating' => (bool) SysVar::get('catalog.show_rating'),
            //прокидываем имя таба к которому надо будет вернуться
            'sTabName' => 'reviews',
            //номер страницы, полученных из урла по правилам роутинга
            'iPage' => $this->getModule()->get('page', 1),
            //прокидываем параметр скрытия формы отзывов
            'hide_form' => $bHideForm,
            'typeShow' => $this->getModuleField('reviewsTemplate'),
        ]);

        if (!$outText) {
            return [];
        }

        return [
            'name' => 'reviews',
            'title' => \Yii::t('catalog', 'reviews'),
            'html' => $outText,
            'tab' => $outText,
        ];
    }

    private function buildTabs()
    {
        $aTabs = [];
        //имя таба
        $sTabName = $this->getModule()->get('tab');

        foreach ($this->aGood['fields'] as $oField) {
            if (!empty($oField['attrs']['show_in_tab']) && $oField['value'] && $oField['html']) {
                $aTabs[] = $oField;
            }
        }

        // модуль вывода отзывов
        if ($this->showingReviews()) {
            $aReviewTab = $this->showReviews();
            if (!empty($aReviewTab)) {
                $aReviewTab['attrs'][catalog\Attr::SHOW_TITLE] = 1;
                $aTabs[] = $aReviewTab;
            }
        }

        //активные табы
        if ($sTabName !== null) {
            foreach ($aTabs as &$tab) {
                if ($tab['name'] === $sTabName) {
                    $tab['active'] = 1;
                }
            }
        }

        return $aTabs;
    }

    /** Показывать отзывы к товару? */
    private function showingReviews()
    {
        return $this->oModule->showReviews or SysVar::get('catalog.guest_book_show');
    }

    /** Вывод блока "Ранее просмотренные товары" */
    private function showRecentlyViewed()
    {
        if (!\Yii::$app->register->moduleExists(RecentlyViewed\Module::getNameModule(), site\Layer::PAGE)) {
            return;
        }

        $sHtml = $this->oModule->createAndExecuteProcess(RecentlyViewed\Module::getNameModule(), RecentlyViewed\Module::className(), [
            'iGoodsId' => $this->aGood['id'],
            'buyFormSection' => $this->getModuleField('buyFormSection'),
            'sTpl' => $this->getModuleField('recentlyViewedTpl'),
            'iOnPage' => $this->getModuleField('recentlyViewedOnPage'),
            'bShow' => (bool) SysVar::get('catalog.goods_recentlyViewed'),
        ]);

        $this->oModule->setData('RecentlyViewed', $sHtml);
    }
}
