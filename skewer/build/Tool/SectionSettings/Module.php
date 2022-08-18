<?php

namespace skewer\build\Tool\SectionSettings;

use skewer\base\section\models\ParamsAr;
use skewer\base\SysVar;
use skewer\build\Adm\News\ShowType;
use skewer\build\Tool;
use yii\base\UserException;

/**
 * Модуль Настроек
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $aData = [
            'aGalleryStatus' => ShowType::getGalleryStatusList(),
            'aSettings' => [
                'menuIcons' => SysVar::get('Menu.ShowIcons'),
                'newsDetailLink' => SysVar::get('News.showDetailLink'),
                'galleryStatus' => SysVar::get('News.galleryStatus'),
                'galleryStatusArticles' => SysVar::get('Articles.galleryStatusArticles'),
                'hasHideDatePublicationInNews' => SysVar::get('News.hasHideDatePublication'),
                'hasHideDatePublicationInArticles' => SysVar::get('Articles.hasHideDatePublication'),
                'showGalleryInNews' => SysVar::get('News.hideGallery'),
                'onCheckSizeOpenGraphImage' => SysVar::get('OpenGraph.onCheckSizeImage'),
                'lock_section_flag' => SysVar::get('lock_section_flag'),
                'favicon_validate' => SysVar::get('favicon_validate'),
                'default_img' => SysVar::get('Gallery.DefaultImg'),
                'data_end_service' => SysVar::get('Page.data_end_service'),
                'warranty_support' => SysVar::get('Page.warranty_support'),
                'min_site_width_for_form' => SysVar::get('Page.min_site_width_for_form'),
                'hide_adm_copyright' => SysVar::get('Page.hide_adm_copyright'),
//                'image_change_effect' => SysVar::get('Page.image_change_effect'),
                'not_save_image_fancybox' => SysVar::get('Page.not_save_image_fancybox'),
                'consent_to_processing' => SysVar::get('Page.consent_to_processing'),
                'showRatingReview' => SysVar::get('Review.showRating'),
                'hideGalleryReview' => SysVar::get('Review.HideGalleryReview'),
                'mode404' => SysVar::get('site.mode404'),
                'checkPolicy' => SysVar::get('tree.checkPolicy'),
            ],
        ];

        $this->render(new Tool\SectionSettings\view\Index($aData));
    }

    /**
     * @throws UserException
     */
    protected function actionSave()
    {
        SysVar::set('Menu.ShowIcons', $this->getInDataVal('menuIcons'));
        SysVar::set('News.showDetailLink', $this->getInDataVal('newsDetailLink'));
        SysVar::set('News.hasHideDatePublication', $this->getInDataVal('hasHideDatePublicationInNews'));
        SysVar::set('Articles.hasHideDatePublication', $this->getInDataVal('hasHideDatePublicationInArticles'));
        SysVar::set('News.galleryStatus', $this->getInDataVal('galleryStatus'));
        SysVar::set('Articles.galleryStatusArticles', $this->getInDataVal('galleryStatusArticles'));
        SysVar::set('OpenGraph.onCheckSizeImage', $this->getInDataVal('onCheckSizeOpenGraphImage'));
        SysVar::set('lock_section_flag', $this->getInDataVal('lock_section_flag'));
        SysVar::set('favicon_validate', $this->getInDataVal('favicon_validate'));
        SysVar::set('Gallery.DefaultImg', $this->getInDataVal('default_img'));
        SysVar::set('Page.type_list_of_sections', $this->getInDataVal('type_list_of_sections'));
        SysVar::set('Page.data_end_service', $this->getInDataVal('data_end_service'));
        SysVar::set('Page.warranty_support', $this->getInDataVal('warranty_support'));
        SysVar::set('Page.hide_adm_copyright', $this->getInDataVal('hide_adm_copyright'));
        SysVar::set('Review.showRating', $this->getInDataVal('showRatingReview'));
        SysVar::set('Review.HideGalleryReview', $this->getInDataVal('hideGalleryReview'));
        SysVar::set('site.mode404', $this->getInDataVal('mode404'));
        SysVar::set('tree.checkPolicy', $this->getInDataVal('checkPolicy'));

        if ($sTpl = $this->getInDataVal('template_gallery_detail')) {
            ParamsAr::updateAll(['value' => $sTpl], ['group' => 'content', 'name' => 'template_detail']);
        }

        $iMinSiteWidth = $this->getInDataVal('min_site_width_for_form');

        if ($iMinSiteWidth < 0) {
            throw new UserException(\Yii::t('page', 'min_site_width_for_form_error'));
        }
        SysVar::set('Page.min_site_width_for_form', $this->getInDataVal('min_site_width_for_form'));
//        SysVar::set('Page.image_change_effect',$this->getInDataVal('image_change_effect'));
        SysVar::set('Page.not_save_image_fancybox', $this->getInDataVal('not_save_image_fancybox'));
        SysVar::set('Page.consent_to_processing', $this->getInDataVal('consent_to_processing'));

        $this->actionInit();
    }
}
