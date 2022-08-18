<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 17:44.
 */

namespace skewer\build\Tool\Import\view;

use skewer\build\Tool\Import\View;

class FieldsForm extends ImportForm
{
    public $oTemplate;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        View::getFieldsForm($this->_form, $this->oTemplate);
        $this->_form->buttonSave('saveFields');
        $this->addStateButton('fields');
        $this->_form->setTrackChanges(false);
    }
}
