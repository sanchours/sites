<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 15:09.
 */

namespace skewer\build\Tool\Slider\view;

use skewer\build\Tool\Slider\Api;
use skewer\components\ext\view\FormView;

class editBannerForm extends FormView
{
    public $oItem;

    public $iBannerId;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'id', 'hide')
            ->field('title', \Yii::t('slider', 'title'), 'string', ['listColumns' => ['flex' => 1]])
            ->fieldSelect('section', \Yii::t('slider', 'section'), Api::getSectionTitle(), [], false)
            ->field('on_include', \Yii::t('slider', 'on_include'), 'check')
            ->fieldSelect('bullet', \Yii::t('slider', 'fotorama_nav'), Api::getNavigations(), [], false)
            ->fieldSelect('scroll', \Yii::t('slider', 'fotorama_arrows'), Api::getArrows(), [], false)
            ->fieldSelect('link_target', \Yii::t('slider', 'general_link_target_type'), Api::getLinkTargetTypes(), [], false)
            ->field('active', \Yii::t('slider', 'active'), 'check')
            ->setValue($this->oItem)
            ->buttonSave('saveBanner');

        if ($this->iBannerId) {
            $this->_form->buttonEdit('slideList', \Yii::t('slider', 'editSlides'));
        }

        $this->_form->buttonCancel('bannerList');
    }
}
