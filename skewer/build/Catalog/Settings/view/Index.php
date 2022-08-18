<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 30.12.2016
 * Time: 15:10.
 */

namespace skewer\build\Catalog\Settings\view;

use skewer\components\ext\view\FormView;
use skewer\components\filters\Api;

class Index extends FormView
{
    public $sGoodsInclude;
    public $sGoodsRelated;
    public $sGoodsModifications;
    public $bHidePriceFractional;
    public $iCountShowGoods;
    public $iParametricSearch;
    public $iShadowParamFilter;
    public $iGuestBookShow;
    public $sCurrencyType;
    public $iShowRating;
    public $iRandomRelated;
    public $iRandomRelatedCount;
    public $iHideReviewForm;
    public $iRedirectHideGood;
    public $iRedirectHideCollection;
    public $iCopyModification;

    public $bRecentlyViewedInstalled = false;
    public $bGoodsRecentlyViewed;
    public $bShowTextPagin;
    public $iFilterType;
    public $iECommerce;
    public $iQuickView;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('goods_include', \Yii::t('catalog', 'goods_include'), 'check')
            ->field('goods_related', \Yii::t('catalog', 'goods_related'), 'check')
            ->field('goods_modifications', \Yii::t('catalog', 'goods_modifications'), 'check')
            ->fieldIf($this->bRecentlyViewedInstalled, 'bGoodsRecentlyViewed', \Yii::t('catalog', 'goods_recentlyViewed'), 'check')
            ->field('hide_price_fractional', \Yii::t('catalog', 'hide_price_fractional'), 'check')
            ->field('countShowGoods', \Yii::t('catalog', 'countShowGoods'), 'int', ['minValue' => 0, 'allowDecimals' => false])
            ->field('parametric_search', \Yii::t('catalog', 'parametric_search'), 'check')
            ->field('shadow_param_filter', \Yii::t('catalog', 'shadow_param_filter'), 'check')
            ->field('guest_book_show', \Yii::t('catalog', 'guest_book_show'), 'check')
            ->field('currency_type', \Yii::t('catalog', 'currency_type'), 'string')
            ->field('show_rating', \Yii::t('catalog', 'show_rating'), 'check')
            ->fieldCheck('random_related', \Yii::t('catalog', 'random_related'))
            ->fieldInt('random_related_count', \Yii::t('catalog', 'random_related_count'), ['minValue' => 0])
            ->fieldCheck('hide_review_form', \Yii::t('catalog', 'hide_form'))
            ->fieldCheck('redirect_hide_good', \Yii::t('catalog', 'redirect_hide_good'))
            ->fieldCheck('redirect_hide_collection', \Yii::t('catalog', 'redirect_hide_collection'))
            ->fieldCheck('copy_modification', \Yii::t('catalog', 'copy_modification'))
            ->fieldCheck('show_text_pagin', \Yii::t('catalog', 'show_text_pagin'))
            ->fieldSelect('filter_type', \Yii::t('catalog', 'filter_type'), Api::getTypes(), [], false)
            ->fieldCheck('e_commerce', \Yii::t('catalog', 'e_commerce'))
            ->fieldCheck('quick_view_show', \Yii::t('catalog', 'quick_view_show'))

            ->buttonSave('save')
            ->setValue([
                'goods_include' => $this->sGoodsInclude,
                'goods_related' => $this->sGoodsRelated,
                'goods_modifications' => $this->sGoodsModifications,
                'hide_price_fractional' => $this->bHidePriceFractional,
                'countShowGoods' => $this->iCountShowGoods,
                'parametric_search' => $this->iParametricSearch,
                'shadow_param_filter' => $this->iShadowParamFilter,
                'guest_book_show' => $this->iGuestBookShow,
                'currency_type' => $this->sCurrencyType,
                'show_rating' => $this->iShowRating,
                'random_related' => $this->iRandomRelated,
                'random_related_count' => $this->iRandomRelatedCount,
                'bGoodsRecentlyViewed' => $this->bGoodsRecentlyViewed,
                'hide_review_form' => $this->iHideReviewForm,
                'redirect_hide_good' => $this->iRedirectHideGood,
                'redirect_hide_collection' => $this->iRedirectHideCollection,
                'copy_modification' => $this->iCopyModification,
                'show_text_pagin' => $this->bShowTextPagin,
                'filter_type' => $this->iFilterType,
                'e_commerce' => $this->iECommerce,
                'quick_view_show' => $this->iQuickView,
            ]);
    }
}
