<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.01.2017
 * Time: 13:14.
 */

namespace skewer\build\Tool\Domains\view;

use skewer\components\ext\view\FormView;

class ShowForm extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->field('id', 'ID', 'hide')
            ->field('domain', \Yii::t('domains', 'domain'), 'string')
            ->field('prim', \Yii::t('domains', 'prim'), 'check')
            ->buttonSave()
            ->buttonBack();
    }
}
