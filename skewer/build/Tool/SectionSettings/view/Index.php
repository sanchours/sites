<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 10:14.
 */

namespace skewer\build\Tool\SectionSettings\view;

use skewer\base\ft\Editor;
use skewer\base\site\Site;
use skewer\build\Adm\Gallery\Api;
use skewer\build\Page\CatalogViewer\State\ListPage;
use skewer\build\Page\Gallery\Module;
use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\view\FormView;
use skewer\components\gallery\Profile;

class Index extends FormView
{
    public $aSettings;
    public $aGalleryStatus;

    public $bRecentlyViewed = false;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('menuIcons', \Yii::t('page', 'menuIcons'), 'check')
            ->field('newsDetailLink', \Yii::t('page', 'newsDetailLink'), 'check')
            ->field('onCheckSizeOpenGraphImage', \Yii::t('page', 'onCheckSizeOpenGraphImage'), 'check')
            ->fieldSelect('galleryStatus', \Yii::t('page', 'galleryStatus'), $this->aGalleryStatus, [], false)
            ->field('hasHideDatePublicationInNews', \Yii::t('page', 'hasHideDateInNews'), 'check')
            ->fieldSelect('galleryStatusArticles', \Yii::t('page', 'galleryStatusArticles'), $this->aGalleryStatus, [], false)
            ->field('hasHideDatePublicationInArticles', \Yii::t('page', 'hasHideDateInArticles'), 'check')
            ->field('onCheckSizeOpenGraphImage', \Yii::t('page', 'onCheckSizeOpenGraphImage'), 'check')
            ->field('onCheckSizeOpenGraphImage', \Yii::t('page', 'onCheckSizeOpenGraphImage'), 'check')
            ->field('lock_section_flag', \Yii::t('page', 'lock_section_flag'), 'check')
            ->fieldSelect('template_gallery_detail', \Yii::t('gallery', 'detailTemplate'), Module::getDetailTemplates(), ['subtext' => \Yii::t('page', 'warning_global_update_tpl_gallery')], true)
            ->field('favicon_validate', \Yii::t('page', 'favicon_validate'), 'check')
            ->field(
                'default_img',
                \Yii::t('page', 'default_img'),
                Editor::GALLERY,
                ['show_val' => Profile::getDefaultId(Profile::TYPE_NEWS)]
            )
            ->fieldCheck('hide_adm_copyright', \Yii::t('page', 'hide_adm_copyright'))
            ->fieldCheck('showRatingReview', \Yii::t('page', 'showRatingReview'))
            ->fieldCheck('hideGalleryReview', \Yii::t('page', 'hideGalleryReview'))
            ->fieldSelect(
                'mode404',
                \Yii::t('page', 'mode404'),
                [
                Site::action_on_error404_respond_only_code404 => \Yii::t('page', 'mode404_for_lp'),
                Site::action_on_error404_respond_page_and_code404 => \Yii::t('page', 'mode404_default'), ],
                [],
                false
            );

        if (CurrentAdmin::isSystemMode()) {
            $this->_form
                ->field('data_end_service', \Yii::t('page', 'data_end_service'), 'date')
                ->fieldCheck('warranty_support', \Yii::t('page', 'warranty_support'))
                ->fieldCheck('checkPolicy', \Yii::t('page', 'check_policy'));
        }

        if ($this->bRecentlyViewed) {
            $this->_form
                ->fieldSelect('recentlyViewedTpl', \Yii::t('catalog', 'recentlyViewedTpl'), ListPage::getTemplates())
                ->fieldInt('recentlyViewedOnPage', \Yii::t('catalog', 'recentlyViewedOnPage'), ['minValue' => 0]);
        }

        $this->_form
            ->fieldInt('min_site_width_for_form', \Yii::t('page', 'min_site_width_for_form'))
//            ->fieldSelect('image_change_effect',\Yii::t('page', 'image_change_effect'),Api::getTransitionEffectFancyBox())
            ->fieldCheck('not_save_image_fancybox', \Yii::t('page', 'not_save_image_fancybox'))
            ->fieldCheck('consent_to_processing', \Yii::t('page', 'consent_to_processing'));

        $this->_form
            ->setValue($this->aSettings)
            ->buttonSave('save')
           // ->buttonCancel() //скрыто из-за бесполезности
;
    }
}
