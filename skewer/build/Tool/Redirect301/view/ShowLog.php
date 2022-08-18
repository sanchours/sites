<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 14.05.2018
 * Time: 17:38.
 */

namespace skewer\build\Tool\Redirect301\view;

use skewer\components\ext\view\FormView;

class ShowLog extends FormView
{
    /** @var string */
    public $text;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('result', \Yii::t('redirect301', 'log_result'), 'show', ['labelAlign' => 'top'])
            ->buttonBack('init')
            ->setValue(['result' => $this->text]);
    }
}
