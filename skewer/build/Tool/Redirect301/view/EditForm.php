<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 15:26.
 */

namespace skewer\build\Tool\Redirect301\view;

use skewer\components\ext\view\FormView;

class EditForm extends FormView
{
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldString('old_url', \Yii::t('redirect301', 'old_url'))
            ->fieldString('new_url', \Yii::t('redirect301', 'new_url'))
           // ->fieldText('input_url', \Yii::t('redirect301', 'input_url'), 300)
           // ->fieldShow('test_results', \Yii::t('redirect301', 'test_results'))
            ->buttonSave('update')
           // ->button('test',\Yii::t('redirect301','test'),'','init',['unsetFormDirtyBlocker'=>true])
            ->buttonCancel('list');

        $this->_form->setValue($this->aData);
    }
}
