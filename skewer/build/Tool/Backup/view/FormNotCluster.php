<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 12.01.2017
 * Time: 18:04.
 */

namespace skewer\build\Tool\Backup\view;

use skewer\components\ext\view\ListView;

class FormNotCluster extends ListView
{
    public $aValues;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('filename_text', \Yii::t('backup', 'filename'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('filename', \Yii::t('backup', 'filename'), 'hide', ['listColumns' => ['flex' => 3]])
            ->field('filesize', \Yii::t('backup', 'size'), 'string', ['listColumns' => ['flex' => 1]])
            ->setValue($this->aValues)
            ->buttonRowConfirm('restoreBackupDB', \Yii::t('backup', 'restore'), \Yii::t('backup', 'restoreBackupText'), 'icon-clone')
            ->buttonRowDelete('deleteBackupDB')
            ->buttonConfirm('addBackupDB', \Yii::t('backup', 'createBackupDB'), \Yii::t('backup', 'createBackupTextDB'), 'icon-add', ['doNotUseTimeout' => true]);
    }
}
