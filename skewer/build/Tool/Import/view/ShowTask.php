<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 03.03.2017
 * Time: 10:37.
 */

namespace skewer\build\Tool\Import\view;

use skewer\build\Tool\Schedule\Api;
use skewer\components\ext\view\FormView;

class ShowTask extends FormView
{
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('schedule_id', 'schedule_id', 'hide');

        Api::addRunTimeSettings($this->_form)

            ->setValue($this->aData)

            ->buttonSave('saveTask')
            ->buttonCancel('headSettings');
    }
}
