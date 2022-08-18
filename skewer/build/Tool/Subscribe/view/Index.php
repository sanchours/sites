<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 14:13.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aItems;
    public $iOnPage;
    public $iPage;
    public $iCount;
    public $bWithConfirmMode;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->headText('<h1>' . \Yii::t('subscribe', 'subList') . '</h1>')
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('subscribe', 'title'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('status', \Yii::t('subscribe', 'status'), 'string', ['listColumns' => ['flex' => 1]]);

        if ($this->iCount) {
            $this->_list->setValue($this->aItems, $this->iOnPage, $this->iPage, $this->iCount);
        } else {
            $this->_list->setValue($this->aItems);
        }

        $this->_list
            ->buttonRowUpdate('editSubscribe')
            ->buttonRowDelete('delSubscribe', 'delete', \Yii::t('subscribe', 'subListText'))

            ->button('users', \Yii::t('subscribe', 'subscribers'), 'icon-user')
            ->button('templates', \Yii::t('subscribe', 'templates'), 'icon-edit')
            ->button('list', \Yii::t('subscribe', 'subscribe_btn'), 'icon-edit')
            ->buttonSeparator()
            ->buttonAddNew('addSubscribeStep1', \Yii::t('subscribe', 'addSubscribe'))
            ->button('SettingsForm', \Yii::t('subscribe', 'settings'), 'icon-configuration');

        if ($this->bWithConfirmMode) {
            $this->_list->buttonEdit('settings', \Yii::t('subscribe', 'language_settings'));
        }
    }
}
