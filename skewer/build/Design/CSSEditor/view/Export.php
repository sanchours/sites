<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 12.01.2017
 * Time: 15:25.
 */

namespace skewer\build\Design\CSSEditor\view;

use skewer\components\ext\view\FormView;

class Export extends FormView
{
    public $sText;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form->getForm()->setAddText($this->sText);
        $this->_form->button('list', 'Назад', 'icon-cancel', 'init', ['unsetFormDirtyBlocker' => false]);
    }
}
