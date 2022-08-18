<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 30.12.2016
 * Time: 15:40.
 */

namespace skewer\build\Catalog\ViewSettings\view;

use skewer\build\Page\CatalogViewer\State\ListPage;
use skewer\build\Page\RecentlyViewed;
use skewer\components\ext\view\FormView;

class Index extends FormView
{
    public $aTplList;
    public $bGoodsRelated;
    public $bGoodsInclude;
    public $aCheckList;

    public $bRecentlyViewed = false;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText(\Yii::t('catalog', 'head_title'))
            ->fieldSelect('template', \Yii::t('catalog', 'listTpl'), $this->aTplList);
        if ($this->bGoodsRelated) {
            $this->_form->fieldSelect('relatedTpl', \Yii::t('catalog', 'relatedTpl'), $this->aTplList);
        }
        if ($this->bGoodsInclude) {
            $this->_form->fieldSelect('includedTpl', \Yii::t('catalog', 'includedTpl'), $this->aTplList);
        }

        $this->_form
            ->fieldInt('onPage', \Yii::t('Catalog', 'listCnt'), ['subtext' => \Yii::t('catalog', 'onPage_label'), 'minValue' => 0])
            ->fieldSelect('showFilter', \Yii::t('Catalog', 'showListFilters'), $this->aCheckList, [], false)
            ->fieldSelect('showSort', \Yii::t('Catalog', 'showListSort'), $this->aCheckList, [], false);

        if ($this->bRecentlyViewed) {
            $this->_form
                ->fieldSelect('recentlyViewedTpl', \Yii::t('catalog', 'recentlyViewedTpl'), $this->aTplList)
                ->fieldInt('recentlyViewedOnPage', \Yii::t('catalog', 'recentlyViewedOnPage'), ['minValue' => 0, 'maxValue' => RecentlyViewed\Module::getMaxCountGoodOnPage()])
                ->fieldSelect('sectionRecentlyViewedTpl', \Yii::t('catalog', 'section_recentlyViewedTpl'), ListPage::getTemplates())
                ->fieldInt('sectionRecentlyViewedOnPage', \Yii::t('catalog', 'section_recentlyViewedOnPage'), ['minValue' => 0, 'maxValue' => RecentlyViewed\Module::getMaxCountGoodOnPage()]);
        }

        $this->_form
            ->setValue([
                'template' => '',
                'relatedTpl' => '',
                'includedTpl' => '',
                'onPage' => '',
                'showFilter' => '0',
                'showSort' => '0',
                'recentlyViewedTpl' > '',
                'recentlyViewedOnPage' => '0',
                'sectionRecentlyViewedTpl' => '',
                'sectionRecentlyViewedOnPage' => '0',
            ])
            ->buttonConfirm('save', \Yii::t('adm', 'save'), \Yii::t('catalog', 'save_msg'), 'icon-save');
    }
}
