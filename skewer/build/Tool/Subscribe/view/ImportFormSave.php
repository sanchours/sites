<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 15.05.2018
 * Time: 9:16.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class ImportFormSave extends FormView
{
    /** @var string */
    public $mode;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonSave('import')
            ->buttonCancel();

        /** @var \skewer\build\Tool\Subscribe\import\Prototype $oProvider */
        $className = 'skewer\build\Tool\Subscribe\import\Type' . mb_strtoupper($this->mode);
        $provider = new $className();

        $this->_form->headText();

        $this->_form = $provider->getFields($this->_form);
    }
}
