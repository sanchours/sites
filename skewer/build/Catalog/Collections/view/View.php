<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.12.2016
 * Time: 15:18.
 */

namespace skewer\build\Catalog\Collections\view;

use skewer\components\ext\view\ListView;

class View extends ListView
{
    public $sTitle;
    public $bActive;
    public $aValues;
    public $sCardTitle;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->filterText('title', $this->sTitle, \Yii::t('collections', 'field_title'))
            ->filterSelect('active', [
                1 => \Yii::t('page', 'yes'),
                2 => \Yii::t('page', 'no'),
            ], $this->bActive, \Yii::t('collections', 'field_active'))
            ->setFilterAction('view')
            ->fieldShow('id', 'id', 'i', ['listColumns.width' => 40])
            ->fieldString('title', \Yii::t('collections', 'field_title'), ['listColumns.flex' => 2])
            ->fieldCheck('active', \Yii::t('collections', 'field_active'), ['listColumns.flex' => 1])
            ->fieldCheck('on_main', \Yii::t('collections', 'field_on_main'), ['listColumns.flex' => 1])
            ->setValue($this->aValues, $this->onPage, $this->page, $this->total)
            ->buttonAddNew('ItemEdit', \Yii::t('collections', 'add'))
            ->buttonEdit('EditCollection', \Yii::t('collections', 'edit_title'))
            ->buttonEdit('FieldList', \Yii::t('collections', 'edit_structure'))
            ->buttonBack('List')
            ->buttonSeparator()
            ->buttonDeleteMultiple()
            ->buttonRowUpdate('ItemEdit')
            ->buttonRowDelete('ItemRemove')
            ->setEditableFields(['active', 'on_main'], 'ItemFastSave')
            ->showCheckboxSelection();
    }
}
