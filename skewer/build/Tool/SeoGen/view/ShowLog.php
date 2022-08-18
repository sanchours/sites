<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 15.05.2018
 * Time: 16:17.
 */

namespace skewer\build\Tool\SeoGen\view;

use skewer\components\ext\view\FormView;

class ShowLog extends FormView
{
    /** @var string */
    public $baskAction;

    /** @var string */
    public $text;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('result', \Yii::t('seoGen', 'log_result'), 'show', ['labelAlign' => 'top'])
            ->buttonBack($this->baskAction)
            ->setValue(['result' => $this->text]);
    }
}
