<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 15:06.
 */

namespace skewer\build\Tool\Import\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $isSys;
    public $aList;
    public $bIsNotDirImport;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', 'id', $this->isSys ? 'string' : 'hide', ['listColumns' => ['flex' => 1]])
            ->field('title', \Yii::t('import', 'field_title'), 'string', ['listColumns' => ['flex' => 3]])
            ->buttonRow('runImport', \Yii::t('import', 'run_import'), 'icon-reload')
            ->buttonRowUpdate('headSettings');

        if ($this->isSys) {
            $this->_list
                ->buttonRowDelete('delete')
                ->button('settingTrade', \Yii::t('import', '1c_settings'), 'icon-configuration')
                ->buttonAddNew('add');
        }

        $this->_list
            ->setValue($this->aList)
            ->buttonEdit('notifySettingsView', \Yii::t('import', 'notify_settings_button'))
            ->buttonSeparator('->');

        if ($this->bIsNotDirImport) {
            $this->_list
                ->buttonAddNew('addFolder', \Yii::t('import', 'addUploadFolder'));
        }

        if ($this->isSys) {
            $this->_list
                ->button('clearQueue', \Yii::t('import', 'button_label_clearQueue'), 'icon-delete');
        }
    }
}
