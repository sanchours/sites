<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 13:20.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\ListView;

class Templates extends ListView
{
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->headText('<h1>' . \Yii::t('subscribe', 'subscribeList') . '</h1>')
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('subscribe', 'title'), 'string', ['listColumns' => ['flex' => 1]])
            ->setValue($this->aItems)
            ->buttonRowUpdate('editTemplate')
            ->buttonRowDelete('delTemplate', 'allow_do', \Yii::t('subscribe', 'deleteTemplate'))
            ->button('users', \Yii::t('subscribe', 'subscribers'))
            ->button('templates', \Yii::t('subscribe', 'templates'))
            ->button('list', \Yii::t('subscribe', 'subscribe_btn'))
            ->buttonSeparator()
            ->buttonAddNew('editTemplate', \Yii::t('subscribe', 'addTemplate'));
    }
}
