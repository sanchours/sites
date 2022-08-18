<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 16:08.
 */

namespace skewer\build\Tool\Import\view;

use skewer\components\ext\view\FormView;

abstract class ImportForm extends FormView
{
    /**
     * Докидывание кнопок на формы.
     *
     * @param string $state - текущее состояние
     */
    public function addStateButton($state)
    {
        $this->_form
            ->buttonSeparator()
            ->button('runImport', \Yii::t('import', 'run_import'), 'icon-reload')
            ->buttonSeparator()
            ->button('headSettings', \Yii::t('import', 'head_settings_form'), 'icon-edit', 'init', ['disabled' => ($state == 'headSettings')])
            ->button('providerSettings', \Yii::t('import', 'provider_settings_form'), 'icon-edit', 'init', ['disabled' => ($state == 'providerSettings')])
            ->button('fields', \Yii::t('import', 'fields_form'), 'icon-edit', 'init', ['disabled' => ($state == 'fields')])
            ->button('fieldsSettings', \Yii::t('import', 'fields_settings_form'), 'icon-edit', 'init', ['disabled' => ($state == 'fieldsSettings')])
            ->buttonSeparator()
            ->button('showTask', \Yii::t('import', 'task_form'), 'icon-configuration')
            ->button('logList', \Yii::t('import', 'log_list'), 'icon-page')
            ->buttonSeparator()
            ->buttonCancel('list');
    }
}
