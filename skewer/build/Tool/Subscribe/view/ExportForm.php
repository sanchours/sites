<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 31.01.2017
 * Time: 18:22.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext;
use skewer\components\ext\view\FormView;

class ExportForm extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->button(
                'export',
                \Yii::t('subscribe', 'next'),
                ext\docked\Api::iconNext,
                'Export',
                ['unsetFormDirtyBlocker' => true]
            )
            ->buttonCancel()
            ->fieldSelect('mode', \Yii::t('subscribe', 'mode_title'), [
                'csv' => \Yii::t('subscribe', 'csv_file'),
                'text' => \Yii::t('subscribe', 'txt_file'),
            ], [], false)
            ->setValue(['mode' => 'csv']);
    }
}
