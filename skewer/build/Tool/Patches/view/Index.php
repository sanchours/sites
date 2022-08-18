<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 13:25.
 */

namespace skewer\build\Tool\Patches\view;

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
            ->fieldString('patch_uid', \Yii::t('patches', 'patch_uid'))
            ->fieldString('install_date', \Yii::t('patches', 'install_date'), ['listColumns' => ['width' => 150]])
            ->fieldString('description', \Yii::t('patches', 'description'), ['listColumns' => ['flex' => 1]])
            ->setValue($this->aItems)
            ->showCheckboxSelection()
            ->button('installPatches', \Yii::t('patches', 'install'), 'icon-install')
            ->button('installPatchesIgnoreErrors', \Yii::t('patches', 'install_ignore'), 'icon-install')
            ->button('archive', \Yii::t('patches', 'archive'), 'icon-page')
            ->buttonRow('installPatchForm', \Yii::t('patches', 'install'), 'icon-install', 'edit_form')
            ->buttonRow('reInstall', \Yii::t('patches', 'reinstall'), 'icon-reload', 'edit_form')
            ->buttonRow('deactivate', \Yii::t('patches', 'deactivate'), 'icon-delete', 'edit_form');
    }
}
