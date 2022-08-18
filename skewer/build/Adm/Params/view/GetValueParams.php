<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.02.2017
 * Time: 14:30.
 */

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\FormView;

class GetValueParams extends FormView
{
    public $aValue;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldString('value', '')
            ->fieldString('access_level', '')
            ->fieldString('show_val', '')
            ->fieldString('title', '')
            ->setValue($this->aValue);
    }
}
