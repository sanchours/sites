<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 12.01.2017
 * Time: 18:29.
 */

namespace skewer\build\Tool\Backup\view;

use skewer\components\ext\view\FormView;

class RecoverForm extends FormView
{
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->getForm()->setFields($this->aItems);
        $this->_form->setValue([])
            ->button(
                'recover',
                \Yii::t('backup', 'restore'),
                'icon-recover',
                'init',
                ['confirmText' => \Yii::t('backup', 'restoreBackupText'), 'unsetFormDirtyBlocker' => true]
            );
        $this->_form->buttonCancel('init')
            ->buttonSeparator('->');
    }
}
