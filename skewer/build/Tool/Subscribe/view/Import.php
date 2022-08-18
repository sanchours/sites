<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 31.01.2017
 * Time: 17:52.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class Import extends FormView
{
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldShow('import_result', \Yii::t('subscribe', 'import_result'))
            ->setValue($this->aData)
            ->buttonCancel('users');
    }
}
