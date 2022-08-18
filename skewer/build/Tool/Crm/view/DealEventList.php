<?php

namespace skewer\build\Tool\Crm\view;

use skewer\components\ext\view\ListView;

class DealEventList extends ListView
{
    public $aFields;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->headText(sprintf('<h1>%s</h1>', \Yii::t('crm', 'deal_events_list_title')))
            ->fieldShow('id', 'id')
            ->fieldString('title', \Yii::t('crm', 'name_site'), ['listColumns.flex' => 2])
            ->fieldString('title_crm', \Yii::t('crm', 'name_crm'), ['listColumns.flex' => 2])
            ->fieldString('from', \Yii::t('crm', 'from'), ['listColumns.flex' => 1])
            ->fieldString('to', \Yii::t('crm', 'to'), ['listColumns.flex' => 1])
            ->fieldCheck('active', \Yii::t('crm', 'active'))
            ->setValue($this->aFields)
            ->buttonCancel('Init', \Yii::t('crm', 'btn_back'))
            ->buttonRowUpdate('DealEventEdit')
            ->setEditableFields(['active'], 'saveDealEvent');
    }
}
