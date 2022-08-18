<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 14:56.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class AddSubscribeStep1 extends FormView
{
    public $aModel;
    public $sIconNext;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('subscribe', 'getTemplate') . '</h1>')
            ->fieldSelect('tpl', \Yii::t('subscribe', 'letter_template_title'), $this->aModel, [], false)
            ->button(
                'addSubscribeStep2',
                \Yii::t('subscribe', 'next'),
                $this->sIconNext,
                'addSubscribeStep2',
                ['unsetFormDirtyBlocker' => true]
            )
            ->buttonCancel();
    }
}
