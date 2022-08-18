<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 17.01.2017
 * Time: 18:10.
 */

namespace skewer\build\Tool\Gallery\view;

use skewer\components\ext\view\ListView;

class ProfilesList extends ListView
{
    public $bIsSystemMode;
    public $aProfiles;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('title', \Yii::t('gallery', 'profiles_title'), ['listColumns' => ['flex' => 1]])
            ->fieldString('type', \Yii::t('gallery', 'profiles_type'), ['listColumns' => ['flex' => 1]])
            ->fieldIf($this->bIsSystemMode, 'default', \Yii::t('gallery', 'profiles_default'), 'check', ['listColumns' => ['flex' => 1]])
            ->fieldCheck('active', \Yii::t('gallery', 'profiles_active'))
            ->setValue($this->aProfiles ?: [])
            ->setEditableFields(['default', 'active'], 'ListChange')
            ->buttonRowUpdate('addUpdProfile')
            ->buttonAddNew('AddUpdProfile');

        if ($this->bIsSystemMode) {
            $this->_list->buttonRowDelete('delProfile');
        }
    }
}
