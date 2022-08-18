<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 11:38.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\ListView;

class Users extends ListView
{
    public $bWithConfirmMode;

    public $aUsers;
    public $iPage;
    public $iOnPage;
    public $iCount;

    public $bFullBtnMode;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->headText('<h1>' . \Yii::t('subscribe', 'list') . '</h1>')
            ->field('id', 'ID', 'hide')
            ->field('email', 'E-mail', 'string', ['listColumns' => ['flex' => 1]]);

        if ($this->bWithConfirmMode) {
            $this->_list->fieldCheck('confirm', \Yii::t('subscribe', 'confirm'));
        }

        if ($this->iCount) {
            $this->_list->setValue($this->aUsers, $this->iOnPage, $this->iPage, $this->iCount);
        } else {
            $this->_list->setValue($this->aUsers);
        }

        $this->_list
            ->buttonRowUpdate('editUser')
            ->buttonRowDelete('delUser', 'allow_do', \Yii::t('subscribe', 'deleteUser'));

        if ($this->bFullBtnMode) {
            $this->_list
                ->button('users', \Yii::t('subscribe', 'subscribers'))
                ->button('templates', \Yii::t('subscribe', 'templates'))
                ->button('list', \Yii::t('subscribe', 'subscribe_btn'));
        } else {
            $this->_list->button('editTemplate', \Yii::t('subscribe', 'editTemplate'));
        }

        $this->_list
            ->buttonSeparator()
            ->buttonAddNew('editUser', \Yii::t('subscribe', 'addSubscriber'))
            ->buttonAddNew('importFormStep1', \Yii::t('subscribe', 'import_button'))
            ->buttonAddNew('exportForm', \Yii::t('subscribe', 'export_button'))
            ->button('SettingsForm', \Yii::t('subscribe', 'settings'))
            ->setEditableFields(['confirm'], 'saveFromList');
    }
}
