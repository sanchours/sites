<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 15:26.
 */

namespace skewer\build\Tool\Redirect301\view;

use skewer\components\ext\view\FormView;

class ImportForm extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('file', \Yii::t('redirect301', 'file'), 'file')
            ->button('importRun', \Yii::t('redirect301', 'import'), '', 'init', ['unsetFormDirtyBlocker' => true])
            ->buttonCancel('list');
    }
}
