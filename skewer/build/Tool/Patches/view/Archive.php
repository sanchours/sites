<?php

/**
 * Created by PhpStorm.
 * User: ermak
 * Date: 11.05.2018
 * Time: 16:51.
 */

namespace skewer\build\Tool\Patches\view;

use skewer\components\ext\view\ListView;

class Archive extends ListView
{
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('patch_uid', \Yii::t('patches', 'patch_uid'))
            ->fieldString('install_date', \Yii::t('patches', 'install_date'), ['listColumns' => ['width' => 150]])
            ->fieldString('description', \Yii::t('patches', 'description'), ['listColumns' => ['flex' => 1]])
            ->setValue($this->aItems, $this->onPage, $this->page, $this->total)
            ->setFilterAction('archive')
            ->buttonBack();
    }
}
