<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 15:18.
 */

namespace skewer\build\Tool\Payments\view;

use skewer\components\ext\view\FormView;

class Edit extends FormView
{
    public $aPaymentsFields;
    public $sType;
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('active', \Yii::t('payments', 'active'), 'check')
            ->field('test_life', \Yii::t('payments', 'test_life'), 'check');

        foreach ($this->aPaymentsFields as $aField) {
            $aParams = (isset($aField[4])) ? $aField[4] : [];
            $this->_form->field($aField[0], $aField[1], $aField[3], $aParams);
        }

        $this->_form->field('type', $this->sType, 'hide')
            ->setValue($this->aItems)
            ->buttonSave('save')
            ->buttonCancel('list');
    }
}
