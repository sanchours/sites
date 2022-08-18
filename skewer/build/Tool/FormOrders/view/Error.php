<?php

declare(strict_types=1);

namespace skewer\build\Tool\FormOrders\view;

use skewer\components\ext\view\FormView;

class Error extends FormView
{
    public $error;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->getForm()->setTitle('');
        $this->_form->headText($this->error);
    }
}
