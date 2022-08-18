<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 18:37.
 */

namespace skewer\build\Adm\Gallery\view;

use skewer\components\ext\view\ShowView;

class ShowError extends ShowView
{
    public $sErrorText;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->headText($this->sErrorText);
    }
}
