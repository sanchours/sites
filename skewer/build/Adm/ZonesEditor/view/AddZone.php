<?php

namespace skewer\build\Adm\ZonesEditor\view;

use skewer\components\ext\view\FormView;

class AddZone extends FormView
{
    public $aData;
    public $aTypes;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldString('name', \Yii::t('ZonesEditor', 'param_name'))
            ->fieldSelect('type', \Yii::t('ZonesEditor', 'param_type'), $this->aTypes, [], false)
            ->setValue($this->aData)

            ->buttonSave('addZone')
            ->buttonCancel();
    }
}
