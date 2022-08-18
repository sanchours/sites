<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 31.01.2017
 * Time: 16:56.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext;
use skewer\components\ext\view\FormView;

class ImportFormStep1 extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->button(
            'ImportForm',
            \Yii::t('subscribe', 'next'),
            ext\docked\Api::iconNext,
            'ImportForm',
            ['unsetFormDirtyBlocker' => true]
        )
            ->buttonCancel()
            ->fieldSelect('mode', \Yii::t('subscribe', 'mode_title'), [
                'csv' => \Yii::t('subscribe', 'csv_file'),
                'text' => \Yii::t('subscribe', 'text'),
            ], [], false)
            ->setValue(['mode' => 'csv']);
    }
}
