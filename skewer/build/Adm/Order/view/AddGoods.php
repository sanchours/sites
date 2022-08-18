<?php

namespace skewer\build\Adm\Order\view;

use skewer\build\Catalog\Goods\view\ListPrototype;
use skewer\components\catalog\Section;

class AddGoods extends ListPrototype
{
    public function build()
    {
        $this->_list
            ->filterText('filter_title', $this->model->getFilter('title'), \Yii::t('catalog', 'goods_title'))
            ->filterSelect('filter_section', Section::getList(), (int) $this->model->getFilter('section'), \Yii::t('catalog', 'section'))
            ->setFilterAction('showAddGoodsList')
            // добавляем поля
            ->field('title', \Yii::t('catalog', 'goods_title'), 'string', ['listColumns.flex' => 3])
            ->field('price', \Yii::t('catalog', 'goods_price'), 'string', ['listColumns.flex' => 1])
            ->setHighlighting('available_section', \Yii::t('catalog', 'error_no_main_section'))
            // элементы управления
            ->buttonAddNewMultiple('addGoods')
            ->buttonBack('goodsShow')
            ->buttonRowAddNew('addGoods', 'edit_form')
            //Вывод галочек для множественных операций
            ->showCheckboxSelection();
    }
}
