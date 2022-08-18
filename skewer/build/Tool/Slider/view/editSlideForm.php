<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 15:57.
 */

namespace skewer\build\Tool\Slider\view;

use skewer\build\Tool\Slider\Api;
use skewer\components\ext;
use skewer\components\ext\view\FormView;

class editSlideForm extends FormView
{
    public $oItem;
    public $iItemId;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'id', 'hide')
            ->field('img', '', 'hide')
            ->fieldSpec('canv', \Yii::t('slider', 'editSlide'), 'SlideShower', $this->oItem)
            ->field('active', \Yii::t('slider', 'active'), 'check')
            ->field('slide_link', \Yii::t('slider', 'slides_link'), 'string')
            ->fieldSelect('link_target', \Yii::t('slider', 'link_target_type'), Api::getLinkTargetTypes(), [], false)
            ->setValue($this->oItem)
            ->buttonSave('saveSlide')
            ->buttonCustomExt(
                ext\docked\UserFile::create(\Yii::t('slider', 'backLoad') . '!', 'SlideLoadImage')
                    ->setIconCls(ext\docked\Api::iconAdd)
                    ->setAddParam('slideId', $this->iItemId)
            );

        if ($this->iItemId) {
            $this->_form->buttonEdit('editSlideText', \Yii::t('slider', 'editSlideText'));
        }

        $this->_form->buttonCancel('slideList');
    }
}
