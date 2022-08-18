<?php

namespace skewer\build\Catalog\Goods\view;

use skewer\components\catalog\Section;

/**
 * Построение интерфейса списка товаров для добавления в комплект
 * Class AddIncludedList.
 */
class AddIncludedList extends ListPrototype
{
    public function build()
    {
        $this->_list
            // добавляем фильтр
            ->filterText('filter_title', $this->model->getFilter('title'), \Yii::t('catalog', 'goods_title'))
            ->filterSelect('filter_section', Section::getList(), (int) $this->model->getFilter('section'), \Yii::t('catalog', 'section'))
            ->setFilterAction('AddIncludedItem')

            // добавляем поля
            ->field('title', \Yii::t('catalog', 'goods_title'), 'string', ['listColumns.flex' => 3])
            ->field('price', \Yii::t('catalog', 'goods_price'), 'string', ['listColumns.flex' => 1])
            ->setHighlighting('available_section', \Yii::t('catalog', 'error_no_main_section'))

            // элементы управления
            ->buttonAddNewMultiple('linkIncludedItem')
            ->buttonBack('IncludedItems')

            ->buttonRowAddNew('linkIncludedItem', 'edit_form')

            // Вывод галочек для множественный операций
            ->showCheckboxSelection();
    }
}
