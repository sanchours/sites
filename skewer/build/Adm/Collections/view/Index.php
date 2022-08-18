<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.12.2016
 * Time: 16:06.
 */

namespace skewer\build\Adm\Collections\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aData;
    public $bIsSystemMode;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('title', \Yii::t('Collections', 'column_title'), ['listColumns' => ['flex' => 1]])
            ->buttonRowUpdate('AddEditCollection')
            ->buttonRowDelete('DeleteCollection')
            ->setValue($this->aData);
        if ($this->bIsSystemMode) {
            $this->_list
                ->buttonAddNew('AddEditCollection')
                ->button('EditLayers', \Yii::t('ZonesEditor', 'button_show'), 'icon-view');
        }
    }
}
