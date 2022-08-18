<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 15:00.
 */

namespace skewer\build\Tool\Payments\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('title', \Yii::t('payments', 'title'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('value', \Yii::t('payments', 'active'), 'check', ['listColumns' => ['flex' => 1]])
            ->buttonEdit('Settings', \Yii::t('payments', 'settings'))
            ->setValue($this->aItems)
            ->setEditableFields(['value'], 'saveActive')
            ->buttonRowUpdate('edit');
    }
}
