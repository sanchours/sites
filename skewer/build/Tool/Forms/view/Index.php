<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $forms;

    public function build()
    {
        $this->_module->setPanelName(\Yii::t('forms', 'form_list'));

        $this->_list
            ->fieldString('settings_title', \Yii::t('forms', 'form_title'), [
                'listColumns' => ['flex' => 1],
            ])
            ->field(
                'handler_title',
                \Yii::t('forms', 'form_handler_type'),
                'string',
                ['listColumns' => ['flex' => 1]]
            )
            ->fieldString('handler_value', \Yii::t('forms', 'form_handler_value'), [
                'listColumns' => ['flex' => 1],
            ])
            ->setHighlighting(
                'settings_system',
                \Yii::t('forms', 'form_sys'),
                '1',
                'color: #999999'
            )
            ->setValue($this->forms)
            ->buttonAddNew('CreateForm', \Yii::t('forms', 'add_new_form'))
            ->buttonRowUpdate('Fields')
            ->buttonRow('Clone', \Yii::t('adm', 'clone'), 'icon-clone')
            ->buttonRowDelete('delete');
    }
}
