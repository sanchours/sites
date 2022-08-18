<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 17:14.
 */

namespace skewer\build\Tool\Import\view;

use skewer\build\Tool\Import\View;

class ProviderSettingsForm extends ImportForm
{
    public $oTemplate;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        View::getProviderForm($this->_form, $this->oTemplate);
        $this->_form->buttonSave('saveProviderSettings');
        $this->addStateButton('providerSettings');
        $this->_form->setTrackChanges(false);
    }
}
