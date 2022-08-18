<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 13:58.
 */

namespace skewer\build\Tool\Redirect301\view;

use skewer\components\ext\view\FormView;

class AddForm extends FormView
{
    public $bNotNewItem;
    public $aData;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id')
            ->fieldString('old_url', \Yii::t('redirect301', 'old_url'))
            ->fieldString('new_url', \Yii::t('redirect301', 'new_url'));
        //->fieldText('input_url', \Yii::t('redirect301', 'input_url'), 300)
        // ->fieldShow('test_results', \Yii::t('redirect301', 'test_results'));

        if ($this->bNotNewItem) {
            $this->_form->fieldHide('id')->buttonSave('update');
        } else {
            $this->_form->buttonSave('add');
        }

        $this->_form
            //->button('test',\Yii::t('redirect301','test'),'','init',['unsetFormDirtyBlocker'=>true])
            ->buttonCancel('list');

        $this->_form->setValue($this->aData);
    }
}
