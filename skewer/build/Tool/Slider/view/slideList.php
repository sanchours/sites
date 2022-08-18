<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 15:38.
 */

namespace skewer\build\Tool\Slider\view;

use skewer\components\ext\view\ListView;

class slideList extends ListView
{
    public $aItems;
    public $iCurrentBanner;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->field('preview_img', '', 'addImg', ['listColumns' => ['flex' => 1]])
            ->field('active', \Yii::t('slider', 'active'), 'check')
            ->field('slide_link', \Yii::t('slider', 'slides_link'), 'string')
            ->setEditableFields(['active'], 'saveSlide')
            ->enableDragAndDrop('sortSlideList')
            ->setValue($this->aItems)
            ->buttonAddNew('editSlideForm', \Yii::t('slider', 'addSlide'));

        if (!$this->iCurrentBanner) {
            $this->_list
                ->buttonEdit('editBannerForm', \Yii::t('slider', 'editBanner'))
                ->buttonBack('bannerList');
        }

        $this->_list
            ->buttonRowUpdate('editSlideForm')
            ->buttonRowConfirm('delSlide', \Yii::t('adm', 'del'), \Yii::t('slider', 'delete_slide'), 'icon-delete');
    }
}
