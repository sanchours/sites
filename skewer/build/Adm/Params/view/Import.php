<?php

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\FormView;

class Import extends FormView
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->fieldString('fileName', \Yii::t('params', 'file_name'))
            ->buttonSave('import', \Yii::t('params', 'do_import'))
            ->buttonCancel();
    }
}
