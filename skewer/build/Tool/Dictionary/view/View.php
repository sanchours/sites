<?php

namespace skewer\build\Tool\Dictionary\view;

use skewer\components\ext\view\ListView;

class View extends ListView
{
    public $aItems;
    public $bBanDelDict;
    public $bBanEditDict;
    public $titleDict;
    public $titleName;
    public $aliasName;

    public function build()
    {
        $this->_list
            ->fieldShow('id', 'id', 'i', ['listColumns.width' => 40])
            ->fieldString('title', $this->titleName, ['listColumns.flex' => 1])
            ->fieldString('alias', $this->aliasName, ['listColumns.flex' => 1])
            ->setValue($this->aItems, $this->onPage, $this->page, $this->total)
            ->setFilterAction('View')
            ->buttonRowUpdate('ItemEdit')
            ->buttonRowDelete('ItemRemove')
            ->buttonAddNew('ItemEdit', \Yii::t('dict', 'add'))
            ->setEditableFields(['title'], 'ItemSave');

        if (!$this->bBanEditDict) {
            $this->_list
                ->buttonEdit('FieldList', \Yii::t('dict', 'struct'));
            $this->_list->enableDragAndDrop('sort');
        }

        $this->_list->buttonCancel('List', \Yii::t('dict', 'back'));

        if (!$this->bBanDelDict) {
            $this->_list
                ->buttonSeparator('->')
                ->buttonConfirm('Remove', \Yii::t('dict', 'del'), \Yii::t('dict', 'del_dict', $this->titleDict), 'icon-delete');
        }
    }
}
