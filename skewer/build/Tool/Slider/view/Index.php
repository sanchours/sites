<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 14:59.
 */

namespace skewer\build\Tool\Slider\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $items;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->field('title', \Yii::t('slider', 'title'), 'string')
            ->field('preview_img', '', 'addImg', ['listColumns' => ['flex' => 1]])
            ->field('active', \Yii::t('slider', 'active'), 'check')
            ->setEditableFields(['active'], 'saveBanner');

        $this->_list->setValue($this->items);

        $this->_list
            ->buttonAddNew('editBannerForm', \Yii::t('slider', 'addBanner'))
            ->buttonEdit('toolsForm', \Yii::t('slider', 'displaySettings'))
            ->buttonRowUpdate('SlideList')
            ->buttonRowDelete('delBanner');
    }
}
