<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 16:21.
 */

namespace skewer\build\Tool\Policy\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aOutItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('title', \Yii::t('auth', 'policytitle'), ['listColumns' => ['flex' => 1]])
            ->fieldCheck('active', \Yii::t('auth', 'active'), ['listColumns' => ['width' => 90, 'align' => 'center']])
            ->buttonRowUpdate()
            ->buttonRowDelete()
            ->buttonRow('clone', \Yii::t('auth', 'clone_button'), 'icon-clone')
            ->buttonAddNew('show')
            ->setValue($this->aOutItems);
    }
}
