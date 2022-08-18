<?php

namespace skewer\build\Tool\Auth\view;

use skewer\components\ext\view\FormView;

class ActivateStatement extends FormView
{
    /** @var array список статусов */
    public $list = [];

    /** @var string текущее значение */
    public $value = '';

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('status', \Yii::t('auth', 'activate_status'), $this->list, [], false)

            ->setValue(['status' => $this->value])

            ->buttonSave('saveStatement')

            ->buttonCancel();
    }
}
