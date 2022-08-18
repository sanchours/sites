<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 14:27.
 */

namespace skewer\build\Adm\HTMLBanners\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $items = [];

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->field('title', \Yii::t('HTMLBanners', 'field_title'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('location', \Yii::t('HTMLBanners', 'field_location'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('active', \Yii::t('HTMLBanners', 'field_active'), 'check', ['listColumns' => ['flex' => 1]])
            ->field('on_main', \Yii::t('HTMLBanners', 'field_onmain'), 'check', ['listColumns' => ['flex' => 1]])
            ->field('on_allpages', \Yii::t('HTMLBanners', 'field_allpages'), 'check', ['listColumns' => ['flex' => 1]])
            ->field('on_include', \Yii::t('HTMLBanners', 'field_include'), 'check', ['listColumns' => ['flex' => 1]])
            ->buttonRowUpdate()
            ->buttonRowDelete()
            ->buttonAddNew('new')
            ->button('sort', \Yii::t('adm', 'sort'), 'icon-add');

        $this->_list->widget('location', '\\skewer\\build\\Adm\\HTMLBanners\\Api', 'getBannerLocation');

        $this->_list->setValue($this->items);
        $this->_list->setEditableFields(['active', 'on_main', 'on_allpages', 'on_include'], 'saveFromList');
    }
}
