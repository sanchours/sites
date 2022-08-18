<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.02.2017
 * Time: 11:29.
 */

namespace skewer\build\Tool\Users\view;

use skewer\components\ext\view\FormView;

class Pass extends FormView
{
    public $aItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id')
            ->fieldShow('login', \Yii::t('auth', 'login'))
            ->fieldString('pass', \Yii::t('auth', 'pass'), ['view' => 'pass'])
            ->fieldString('pass2', \Yii::t('auth', 'duplPass'), ['view' => 'pass'])

            ->setValue($this->aItem)

            ->buttonSave('savePass')
            ->buttonCancel('show');
    }
}
