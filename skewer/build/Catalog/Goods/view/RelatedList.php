<?php

namespace skewer\build\Catalog\Goods\view;

/**
 * Построитель списка связанных товаров
 * Class RelatedList.
 */
class RelatedList extends ListPrototype
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
            ->setFilterAction('RelatedItems') // Для работы постраничника
            ->buttonAddNew('AddRelatedItem')
            ->buttonBack('Edit')
            ->buttonSeparator()
            ->buttonDeleteMultiple('removeRelatedItem');
        //drag-and-drop
        $this->setSorting('sortRelated');

        // Вывод галочек для множественный операций
        $this->_list->showCheckboxSelection();
    }
}
