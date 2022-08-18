<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 14:53.
 */

namespace skewer\build\Tool\Redirect301\view;

use skewer\components\ext\view\FormView;

class TestAll extends FormView
{
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldText('input_url', \Yii::t('redirect301', 'input_url'), 300)
            ->fieldShow('test_results', \Yii::t('redirect301', 'test_results'))
            ->button('test', \Yii::t('redirect301', 'test'), '', 'init', ['unsetFormDirtyBlocker' => true])
            ->buttonCancel('list');

        $this->_form->setValue($this->aData);
    }
}
