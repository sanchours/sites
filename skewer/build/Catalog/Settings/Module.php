<?php

namespace skewer\build\Catalog\Settings;

use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\build\Catalog\LeftList\ModulePrototype;
use skewer\build\Page\RecentlyViewed;
use skewer\components\catalog;
use skewer\components\seo\Service;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Модуль настройки каталога.
 */
class Module extends ModulePrototype
{
    public function actionInit()
    {
        $goodsInclude = SysVar::get('catalog.goods_include');
        $goodsRelated = SysVar::get('catalog.goods_related');
        $goodsRecentlyViewed = SysVar::get('catalog.goods_recentlyViewed');
        $bHidePriceFractional = SysVar::get('catalog.hide_price_fractional');
        $iCountShowGoods = SysVar::get('catalog.countShowGoods');
        $iParametricSearch = SysVar::get('catalog.parametricSearch');
        $iShadowParamFilter = SysVar::get('catalog.shadow_param_filter');

        $iGuestBookShow = SysVar::get('catalog.guest_book_show');
        $sCurrencyType = SysVar::get('catalog.currency_type');
        $iShowRating = SysVar::get('catalog.show_rating');
        $iRandomRelated = SysVar::get('catalog.random_related');
        $iRandomRelatedCount = SysVar::get('catalog.random_related_count');
        $iHideReviewForm = SysVar::get('catalog.hide_review_form');
        $iRedirectHideGood = SysVar::get('catalog.redirect_hide_good');
        $iRedirectHideCollection = SysVar::get('catalog.redirect_hide_collection');
        $iCopyModification = SysVar::get('catalog.copy_modification');
        $bShowTextPagin = SysVar::get('catalog.show_text_pagin');
        $iFilterType = SysVar::get('filter.filter_type');
        $iECommerce = SysVar::get('catalog.e_commerce');
        $iQuickView = SysVar::get('catalog.quick_view_show');

        if (($goodsModifications = SysVar::get('catalog.goods_modifications')) == null) {
            $goodsModifications = 0;
        }

        $aData = [
            'sGoodsInclude' => $goodsInclude,
            'sGoodsRelated' => $goodsRelated,
            'sGoodsModifications' => $goodsModifications,
            'bHidePriceFractional' => $bHidePriceFractional,
            'iCountShowGoods' => $iCountShowGoods,
            'iParametricSearch' => $iParametricSearch,
            'iShadowParamFilter' => $iShadowParamFilter,
            'iGuestBookShow' => $iGuestBookShow,
            'sCurrencyType' => $sCurrencyType,
            'iShowRating' => $iShowRating,
            'iRandomRelated' => $iRandomRelated,
            'iRandomRelatedCount' => $iRandomRelatedCount,
            'iHideReviewForm' => $iHideReviewForm,
            'iRedirectHideGood' => $iRedirectHideGood,
            'iRedirectHideCollection' => $iRedirectHideCollection,
            'iCopyModification' => $iCopyModification,
            'bShowTextPagin' => $bShowTextPagin,
            'iFilterType' => $iFilterType,
            'iECommerce' => $iECommerce,
            'iQuickView' => $iQuickView,
        ];

        if (\Yii::$app->register->moduleExists(RecentlyViewed\Module::getNameModule(), Layer::PAGE)) {
            $aData['bRecentlyViewedInstalled'] = true;
            $aData['bGoodsRecentlyViewed'] = $goodsRecentlyViewed;
        }

        $this->render(new view\Index($aData));
    }

    public function actionSave()
    {
        $iFilterType = ArrayHelper::getValue($this->get('data'), 'filter_type', '');

        $countShowGoods = $this->getInDataValInt('countShowGoods');

        if ($countShowGoods <= 0) {
            throw new UserException(\Yii::t('catalog', 'countShowGoodsError'));
        }
        if ($this->getInDataValInt('random_related_count') <= 0) {
            throw new UserException(\Yii::t('catalog', 'countShowGoodsError'));
        }
        // Если были включены или выключены аналоги(модификации), то обновить поисковой индекс и карту сайта
        if ((int) $this->getInDataVal('goods_modifications') != (int) SysVar::get('catalog.goods_modifications')) {
            // ! Этот параметр нужно установить раньше обновления поискового индекса товара, т. к. используется там
            SysVar::set('catalog.goods_modifications', (int) $this->getInDataVal('goods_modifications'));

            catalog\Api::deactiveModificationsFromSearch();
            Service::makeSearchIndex();
            Service::updateSiteMap();
        }

        $iQuickViewShow = (int) $this->getInDataVal('quick_view_show');
        if ($iQuickViewShow && ($iQuickViewShow != (int) SysVar::get('catalog.quick_view_show'))) {
            SysVar::set('catalog.quick_view_show', $iQuickViewShow);
            catalog\Card::clearCache();
        }

        if (($bGoodsRecentlyViewed = $this->getInDataVal('bGoodsRecentlyViewed', null)) !== null) {
            SysVar::set('catalog.goods_recentlyViewed', (int) $bGoodsRecentlyViewed);
        }

        SysVar::set('catalog.goods_include', (int) $this->getInDataVal('goods_include'));
        SysVar::set('catalog.goods_related', (int) $this->getInDataVal('goods_related'));
        SysVar::set('catalog.hide_price_fractional', (int) $this->getInDataVal('hide_price_fractional'));
        SysVar::set('catalog.parametricSearch', (int) $this->getInDataVal('parametric_search'));
//        SysVar::set('catalog.hide2lvlGoodsLinks', (int)$this->getInDataVal('hide2lvlGoodsLinks'));
//        SysVar::set('catalog.hideBuy1lvlGoods', (int)$this->getInDataVal('hideBuy1lvlGoods'));
        SysVar::set('catalog.shadow_param_filter', (int) $this->getInDataVal('shadow_param_filter'));
        SysVar::set('catalog.quick_view_show', (int) $this->getInDataVal('quick_view_show'));

        SysVar::set('catalog.countShowGoods', $countShowGoods);
        SysVar::set('catalog.guest_book_show', (int) $this->getInDataVal('guest_book_show'));
        SysVar::set('catalog.currency_type', $this->getInDataVal('currency_type'));
        SysVar::set('catalog.show_rating', (int) $this->getInDataVal('show_rating'));
        SysVar::set('catalog.random_related', (int) $this->getInDataValInt('random_related'));
        SysVar::set('catalog.random_related_count', (int) $this->getInDataValInt('random_related_count'));
        SysVar::set('catalog.hide_review_form', (int) $this->getInDataValInt('hide_review_form'));
        SysVar::set('catalog.redirect_hide_good', (int) $this->getInDataValInt('redirect_hide_good'));
        SysVar::set('catalog.redirect_hide_collection', (int) $this->getInDataValInt('redirect_hide_collection'));
        SysVar::set('catalog.copy_modification', (int) $this->getInDataValInt('copy_modification'));
        SysVar::set('catalog.show_text_pagin', (int) $this->getInDataValInt('show_text_pagin'));
        SysVar::set('filter.filter_type', $iFilterType);
        SysVar::set('catalog.e_commerce', (int) $this->getInDataValInt('e_commerce'));
        \Yii::$app->router->updateModificationDateSite();

        $this->actionInit();
    }
}
