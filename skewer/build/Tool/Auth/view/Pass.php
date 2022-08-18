<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.12.2016
 * Time: 12:32.
 */

namespace skewer\build\Tool\Auth\view;

use skewer\components\ext\view\FormView;

class Pass extends FormView
{
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonSave('savePass')
            ->buttonBack()
            ->fieldHide('id', 'id')
            ->field('pass', \Yii::t('auth', 'password'), 'pass')
            ->field('wpass', \Yii::t('auth', 'wpassword'), 'pass')
            ->setValue($this->aData);
    }
}
