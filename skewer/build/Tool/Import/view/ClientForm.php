<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 16:37.
 */

namespace skewer\build\Tool\Import\view;

use skewer\components\ext\view\FormView;

class ClientForm extends FormView
{
    public $sGroup;
    public $bEqualTypes;
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide', ['groupTitle' => $this->sGroup])
            ->field('title', \Yii::t('import', 'field_title'), 'show', ['groupTitle' => $this->sGroup])
            ->fieldCheck('send_notify', \Yii::t('import', 'field_send_notify'), ['groupTitle' => $this->sGroup])
            ->fieldCheck('send_error', \Yii::t('import', 'send_error'), ['groupTitle' => $this->sGroup]);

        if ($this->bEqualTypes) {
            $this->_form->field('source_file', \Yii::t('import', 'field_source'), 'file', ['groupTitle' => $this->sGroup]);
        } else {
            $this->_form->field('source_str', \Yii::t('import', 'field_source'), 'string', ['groupTitle' => $this->sGroup]);
        }

        $this->_form
            ->setValue($this->aData)
            ->buttonSave('save')
            ->buttonSeparator()
            ->button('runImport', \Yii::t('import', 'run_import'), 'icon-reload')
            ->buttonSeparator()
            ->button('fieldsSettings', \Yii::t('import', 'fields_settings_form'), 'icon-edit')
            ->buttonSeparator()
            ->button('logList', \Yii::t('import', 'log_list'), 'icon-page')
            ->buttonSeparator()
            ->buttonCancel('list')
            ->setTrackChanges(false);
    }
}
