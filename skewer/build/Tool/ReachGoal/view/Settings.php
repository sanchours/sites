<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 11:14.
 */

namespace skewer\build\Tool\ReachGoal\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $aFields;
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        foreach ($this->aFields as $field) {
            $this->_form->field($field['name'], $field['title'], $field['type']);
        }
        $this->_form->buttonSave('saveSettings')
            ->buttonCancel()
            ->setValue($this->aData);
    }
}
