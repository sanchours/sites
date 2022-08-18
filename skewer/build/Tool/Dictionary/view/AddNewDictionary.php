<?php

namespace skewer\build\Tool\Dictionary\view;

use skewer\components\ext\view\FormView;

class AddNewDictionary extends FormView
{
    public $oCard;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('dict', 'new_dict') . '</h1>')
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('dict', 'dict_name'), ['listColumns.flex' => 1])
            ->fieldString('name', \Yii::t('dict', 'system_name'))
            ->fieldSelect('priority_sort', \Yii::t('dict', 'sort'), ['0' => \Yii::t('dict', 'old'), '1' => \Yii::t('dict', 'new')])
            ->setValue($this->oCard)
            ->buttonSave('Add')
            ->buttonCancel('List');
    }
}
