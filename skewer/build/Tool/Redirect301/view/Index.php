<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 13:08.
 */

namespace skewer\build\Tool\Redirect301\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aRedirects;
    public $aFilter;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->filterText('filter_old_url', $this->aFilter['old_url'], \Yii::t('redirect301', 'filter_old_url'))
            ->filterText('filter_new_url', $this->aFilter['new_url'], \Yii::t('redirect301', 'filter_new_url'))
            ->field('old_url', \Yii::t('redirect301', 'old_url'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('new_url', \Yii::t('redirect301', 'new_url'), 'string', ['listColumns' => ['flex' => 1]])
            ->buttonRowUpdate('editForm')
            ->buttonRowDelete()
            ->buttonAddNew('addForm')
            ->button('testAll', \Yii::t('redirect301', 'test'), 'icon-edit')
            ->button('importForm', \Yii::t('redirect301', 'import'), 'icon-edit')
            ->button('exportForm', \Yii::t('redirect301', 'export'), 'icon-edit')
            ->setValue($this->aRedirects, $this->onPage, $this->page, $this->total)
            ->enableDragAndDrop('sortRedirects');
    }
}
