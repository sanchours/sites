<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.02.2017
 * Time: 12:09.
 */

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\ListView;
use Yii;

class Init extends ListView
{
    public $sFilter;
    public $aItems;
    public $aModuleLangValues;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('name', Yii::t('params', 'name'), ['listColumns' => ['flex' => 5], 'sorted' => true])
            ->fieldString('title', Yii::t('params', 'title'), ['listColumns' => ['flex' => 5]])
            ->fieldString('value', Yii::t('params', 'value'), ['listColumns' => ['flex' => 5]])
            ->fieldString('id', 'ID', ['listColumns' => ['flex' => 1]])
            ->fieldString('parent', Yii::t('params', 'parent'), ['listColumns' => ['flex' => 1]])
            ->filterText('filter', $this->sFilter, Yii::t('params', 'filter'))
            ->setValue($this->aItems)
            ->setEditableFields(['value'], 'save')
            ->setGroups('group')
            ->sortBy('name')
            ->buttonRowCustomJs('ParamsAddObjBtn', '', '', ['state' => 'add_obj'])
            ->buttonRowCustomJs('ParamsEditBtn', '', '', ['state' => 'edit_form'])
            ->buttonRowCustomJs('ParamsDelBtn')
            ->buttonAddNew('add')
            ->buttonDelete()
            ->buttonSeparator()
            ->button('exportForm', Yii::t('params', 'export'))
            ->button('importForm',  Yii::t('params', 'import'))
            ->setModuleLangValues($this->aModuleLangValues);
    }
}
