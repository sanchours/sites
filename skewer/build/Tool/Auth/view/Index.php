<?php

namespace skewer\build\Tool\Auth\view;

use skewer\build\Page\Auth\Api;
use skewer\components\ext\view\ListView;

/**
 * Интерфейс списка клиентов.
 *
 **/
class Index extends ListView
{
    /** @var array Users[] */
    public $items = [];
    public $iStatusFilter = 0;
    // фильтр по тексту
    public $sSearchNameFilter = '';
    public $sSearchEmailFilter = '';
    public $sSearchPhoneFilter = '';

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', \Yii::t('auth', 'id'), 'string')
            ->field('name', \Yii::t('auth', 'name'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('login', \Yii::t('auth', 'email'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('phone', \Yii::t('auth', 'phone'), 'string')
            ->field('reg_date', \Yii::t('auth', 'reg_date'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('user_info', \Yii::t('auth', 'status_edit'), 'string')

            ->filterText('search', $this->sSearchNameFilter, \Yii::t('auth', 'name'))
            ->filterText('email', $this->sSearchEmailFilter, \Yii::t('auth', 'email'))
            ->filterText('phone', $this->sSearchPhoneFilter, \Yii::t('auth', 'contact_phone'))
            ->filterSelect('filter_status', Api::getStatusList(), $this->iStatusFilter, \Yii::t('auth', 'field_status_active'))

            ->buttonAddNew('newUser')
            ->button('editActivateStatement', \Yii::t('auth', 'status_edit'), 'icon-edit', 'editActivateStatement')
            ->button('showMail', \Yii::t('auth', 'mail_status_edit'), 'icon-edit', 'showMail')

            ->buttonRowCustomJs('StatusGroupBtn')
            ->buttonRowUpdate('editUser')
            ->buttonRowDelete();
        $aActiveStatusList = Api::getStatusList();

        foreach ($this->items as &$oCatalog) {
            $oCatalog->login = htmlentities($oCatalog->login);
            $oCatalog->name = htmlentities($oCatalog->name);
            $oCatalog->user_info = (isset($aActiveStatusList[$oCatalog->active])) ? $aActiveStatusList[$oCatalog->active] : $oCatalog->active;
        }
        $this->_list->setValue($this->items, $this->onPage, $this->page, $this->total);

        $this->_list->setEditableFields(['active', 'on_main'], 'saveFromList');
    }
}
