<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 31.01.2017
 * Time: 17:09.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class ImportForm extends FormView
{
    public $oProvider;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonSave('import')
            ->buttonCancel();

        $this->_form = $this->oProvider->getFields($this->_form);
    }
}
