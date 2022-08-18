<?php

namespace skewer\build\Tool\Backup\view;

use skewer\components\ext\view\ListView;

class FormIncluster extends ListView
{
    public $aItems;
    public $bIsApache;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('date', \Yii::t('backup', 'date'), ['listColumns' => ['width' => 120]])
            ->fieldString('mode', \Yii::t('backup', 'mode'), ['listColumns' => ['width' => 50]])
            ->fieldString('size', \Yii::t('backup', 'size'), ['listColumns' => ['width' => 80]])
            ->fieldString('backup_file', \Yii::t('backup', 'backup_file'), ['listColumns' => ['flex' => 1]])
            ->fieldString('comments', \Yii::t('backup', 'comment'), ['listColumns' => ['flex' => 2]])
            ->setValue($this->aItems);
        if ($this->bIsApache) {
            $this->_list->buttonRow('recoverForm', \Yii::t('backup', 'restore'), 'icon-recover');
        }
        $this->_list
            ->buttonRowDelete('remove')
            ->buttonEdit('toolsForm', \Yii::t('backup', 'date_setup'))
            ->buttonSave('CreateBackupForm', \Yii::t('backup', 'createBackup'));
    }
}
