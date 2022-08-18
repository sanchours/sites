<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 14:38.
 */

namespace skewer\build\Adm\HTMLBanners\view;

use skewer\build\Adm\HTMLBanners\Api;
use skewer\components\ext\view\FormView;

class Form extends FormView
{
    public $item;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('HTMLBanners', 'field_title'))
            ->field('content', \Yii::t('HTMLBanners', 'field_content'), 'wyswyg')
            ->fieldSelect('location', \Yii::t('HTMLBanners', 'field_location'), Api::getBannerLocations(), [], false)
            ->fieldSelect('section', \Yii::t('HTMLBanners', 'field_section'), Api::getSectionList(), [], false)
            ->field('sort', \Yii::t('HTMLBanners', 'field_sort'), 'string')
            ->field('active', \Yii::t('HTMLBanners', 'field_active'), 'check')
            ->field('on_main', \Yii::t('HTMLBanners', 'field_onmain'), 'check')
            ->field('on_allpages', \Yii::t('HTMLBanners', 'field_allpages'), 'check')
            ->field('on_include', \Yii::t('HTMLBanners', 'field_include'), 'check')

            ->buttonSave()
            ->buttonBack()

            ->setValue($this->item);
    }
}
