<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 17.02.2017
 * Time: 10:31.
 */

namespace skewer\build\Tool\YandexExport\view;

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
            ->fieldHide('id', 'ID');

        Api::addRunTimeSettings($this->_form)

            ->setValue($this->aData)

            ->buttonSave('saveTask')
            ->buttonCancel();
    }
}
