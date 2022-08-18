<?php

namespace skewer\build\Tool\Dictionary\view;

use skewer\base\site\Layer;
use skewer\components\catalog\model\EntityRow;
use skewer\components\ext\view\ListView;

class Index extends ListView
{
    /** @var EntityRow[] */
    public $aDictionaries;

    public $isSys;
    /** @var string слой */
    public $sLayer;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->fieldShow('id', 'id', 'i', ['listColumns.width' => 40])
            ->fieldString('title', \Yii::t('dict', 'dict_name'), ['listColumns.flex' => 1])
            ->fieldString('name', \Yii::t('dict', 'dict_sys_name'), ['listColumns.flex' => 2]);

        if ($this->isSys) {
            $this->_list
                ->fieldCheck('banEditDict', \Yii::t('dict', 'ban_edit_dict'), ['listColumns.width' => 120]);

            if ($this->sLayer == Layer::TOOL) {
                $this->_list->fieldCheck('banDelDict', \Yii::t('dict', 'ban_del_dict'), ['listColumns.width' => 120]);
            }
        }

        $this->_list
            ->setValue($this->aDictionaries)
            ->buttonAddNew('New', \Yii::t('dict', 'create_dict'))
            ->buttonRowUpdate('View')
            ->setEditableFields(['title'], 'ChangeDictName')
            ->setEditableFields(['banDelDict'], 'ChangeDictBanDel')
            ->setEditableFields(['banEditDict'], 'ChangeDictEditDel');
    }
}
