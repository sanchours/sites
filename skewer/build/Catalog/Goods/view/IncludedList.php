<?php

namespace skewer\build\Catalog\Goods\view;

/**
 * Построитель списка товаров в комплекте
 * Class IncludedList.
 */
class IncludedList extends ListPrototype
{
    public function build()
    {
        // добавляем поля
        $this
            ->addField('title', 'string', ['flex' => 3])
            ->addField('price', 'string', ['flex' => 1])
            ->setHighlighting('available_section', \Yii::t('catalog', 'error_no_main_section'));

        // элементы управления
        $this->_list
            ->setFilterAction('IncludedItems') // Для работы постраничника
            ->buttonAddNew('AddIncludedItem')
            ->buttonBack('Edit')
            ->buttonSeparator()
            ->buttonDeleteMultiple('removeIncludedItem');
        //drag-and-drop
        $this->setSorting('sortRelated');

        // Вывод галочек для множественный операций
        $this->_list->showCheckboxSelection();
    }
}
