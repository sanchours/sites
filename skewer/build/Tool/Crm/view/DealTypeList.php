<?php

namespace skewer\build\Tool\Crm\view;

use skewer\components\ext\view\ListView;

class DealTypeList extends ListView
{
    public $aFields;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->headText(sprintf('<h1>%s</h1>', \Yii::t('crm', 'deal_types_list_title')))
            ->fieldShow('id', 'id')
            ->fieldString('name', \Yii::t('crm', 'name_site'), ['listColumns.flex' => 1])
            ->fieldString('name_crm', \Yii::t('crm', 'name_crm'), ['listColumns.flex' => 1])
            ->fieldCheck('active', \Yii::t('crm', 'active'))
            ->setValue($this->aFields)
            ->buttonCancel('Init', \Yii::t('crm', 'btn_back'))
            ->buttonRowUpdate('DealTypeEdit')
            ->setEditableFields(['active'], 'saveDealType');
    }
}
