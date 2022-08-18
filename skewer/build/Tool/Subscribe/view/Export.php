<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 31.01.2017
 * Time: 19:02.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class Export extends FormView
{
    public $aParams;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonCancel()
            ->fieldShow('out_file', \Yii::t('subscribe', 'out_file'))
            ->setValue(['out_file' => '<a target="_blank" href="http://' . WEBROOTPATH . 'download/?' . http_build_query($this->aParams) . '">File</a>']);
    }
}
