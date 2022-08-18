<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 12:48.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class EditUser extends FormView
{
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('subscribe', 'editSubscriber') . '</h1>')
            ->field('id', 'ID', 'hide')
            ->field('email', 'E-mail', 'string')

            ->buttonSave('saveUser')
            ->buttonCancel('users')
            ->setValue($this->aItems);
    }
}
