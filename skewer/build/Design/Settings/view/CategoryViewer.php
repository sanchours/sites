<?php

namespace skewer\build\Design\Settings\view;

use skewer\components\ext\view\FormView;

class CategoryViewer extends FormView
{
    public $aWidgetList;
    public $sCurrentWidget;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('widget', 'Шаблон', $this->aWidgetList, [], false)
            ->buttonSave('changeCategoryViewer')
            ->buttonCancel();

        $this->_form->setValue([
            'widget' => $this->sCurrentWidget,
        ]);
    }
}
