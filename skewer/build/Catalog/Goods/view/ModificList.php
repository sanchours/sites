<?php

namespace skewer\build\Catalog\Goods\view;

/**
 * Построитель списка модификаций для товара
 * Class ModificList.
 */
class ModificList extends ListPrototype
{
    /** @var string[] Список обязательных уникальных полей */
    protected $aUniqFields = ['id', 'alias', 'active'];

    public function build()
    {
        foreach ($this->model->getFields() as $oField) {
            if ($oField->getAttr('is_uniq') || in_array($oField->getName(), $this->aUniqFields)) {
                if (!in_array($oField->getEditorName(), ['wyswyg', 'html', 'gallery'])) {
                    switch ($oField->getEditorName()) {
                        case 'check':
                            $sTypeName = 'check';
                            break;
                        case 'money':
                            $sTypeName = 'money';
                            break;
                        default:
                            $sTypeName = 'string';
                    }

                    $this->_list->field($oField->getName(), $oField->getTitle(), $sTypeName, ['listColumns' => ['flex' => 1]]);
                }
            }
        }

        $this->setEditableFields($this->model->getEditableFields(), 'EditModificationsItem');

        $this->setSorting('sortModific');

        // элементы управления
        $this->_list
            ->setFilterAction('ModificationsItems') // Для работы постраничника
            ->buttonRowUpdate('EditModificationsItem')
            ->buttonAddNew('EditModificationsItem')
            ->buttonBack('Edit')
            ->buttonSeparator()
            ->buttonDeleteMultiple('DeleteModificationsItem');

        // Вывод галочек для множественный операций
        $this->_list->showCheckboxSelection();
    }
}
