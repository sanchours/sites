<?php

namespace skewer\build\Tool\Dictionary\view;

use skewer\components\ext\view\FormView;

class ItemEdit extends FormView
{
    public $aNotSortFields;
    public $mItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->fieldHide('id', 'id');
        foreach ($this->aNotSortFields as $oField) {
            $this->_form->fieldByEntity($oField);
        }
        $this->_form
            ->setValue($this->mItem ?: [])
            ->buttonSave('ItemSave')
            ->buttonCancel('View');
    }
}
