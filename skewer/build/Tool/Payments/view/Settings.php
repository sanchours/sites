<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 15:18.
 */

namespace skewer\build\Tool\Payments\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $aValues;
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('payment_method', \Yii::t('payments', 'payment_method'), $this->aItems)
            ->setValue($this->aValues)
            ->buttonSave('saveSettings')
            ->buttonCancel('list');
    }
}
