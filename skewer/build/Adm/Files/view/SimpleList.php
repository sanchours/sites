<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 22.02.2017
 * Time: 17:49.
 */

namespace skewer\build\Adm\Files\view;

use skewer\base\ui\builder\ListBuilder;
use skewer\build\Adm\Files\ExtListModule;
use skewer\components\ext\view\ListView;

class SimpleList extends ListView
{
    public $aItems;
    public $bCanSelect;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->_list = new ListBuilder(new ExtListModule());
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('name', \Yii::t('Files', 'name'), ['listColumns' => ['flex' => 1]])
            ->fieldString('size', \Yii::t('Files', 'size'), ['listColumns' => ['flex' => 1]])
            ->fieldString('ext', \Yii::t('Files', 'ext'), ['listColumns' => ['flex' => 1]])
            ->fieldString('modifyDate', \Yii::t('Files', 'modifyDate'), ['listColumns' => ['flex' => 1]])
            ->setGroups('ext')
            ->sortBy('ext')
            ->sortBy('name')
            ->enableSorting()
            ->buttonRowDelete()
            ->setValue($this->aItems)
            ->showCheckboxSelection();

        if ($this->bCanSelect) {
            $this->_list
                ->button('', \Yii::t('Files', 'select'), 'icon-commit', 'selectFile')
                ->buttonSeparator();
        }

        $this->_list->buttonAddNew('addForm', \Yii::t('Files', 'load'))
            ->button('', \Yii::t('Files', 'showFilesLink'), 'icon-link', 'copy_filelink', ['unsetFormDirtyBlocker' => true])
            ->buttonSeparator('->')
            ->buttonDeleteMultiple('');
    }
}
