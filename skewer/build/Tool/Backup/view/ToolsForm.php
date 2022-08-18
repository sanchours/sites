<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 12.01.2017
 * Time: 18:13.
 */

namespace skewer\build\Tool\Backup\view;

use skewer\components\ext\view\FormView;

class ToolsForm extends FormView
{
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->getForm()->setFields($this->aItems);
        $this->_form->setValue([]);
        $this->_form->buttonSave('saveTools');
        $this->_form->button('init', \Yii::t('backup', 'backToList'), 'icon-cancel', 'init');
    }
}
