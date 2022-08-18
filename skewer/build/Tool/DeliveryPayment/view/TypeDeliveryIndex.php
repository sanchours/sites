<?php

namespace skewer\build\Tool\DeliveryPayment\view;

use skewer\base\ft\Editor;
use skewer\components\ext\view\ListView;

class TypeDeliveryIndex extends ListView
{
    public $aItems = [];
    public $aEditFields;
    public $paidDelivery;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', \Yii::t('deliverypayment', 'field_id'), Editor::INTEGER, ['listColumns.width' => 40])
            ->field('title', \Yii::t('deliverypayment', 'field_title'), Editor::STRING, ['listColumns' => ['flex' => 1]])
            ->field('alias', \Yii::t('deliverypayment', 'field_alias'), Editor::STRING, ['listColumns' => ['flex' => 1]])
            ->field('price', \Yii::t('deliverypayment', 'field_price'), Editor::MONEY, ['listColumns' => ['flex' => 1, 'disabled' => !$this->paidDelivery]])
            ->field('active', \Yii::t('deliverypayment', 'field_active'), Editor::CHECK, ['listColumns' => ['flex' => 1]])
            ->field('free_shipping', \Yii::t('deliverypayment', 'field_free_shipping'), Editor::CHECK, ['listColumns' => ['flex' => 1, 'disabled' => !$this->paidDelivery]])
            ->field('coord_deliv_costs', \Yii::t('deliverypayment', 'field_coord_deliv_costs'), Editor::CHECK, ['listColumns' => ['flex' => 1, 'disabled' => !$this->paidDelivery]])
            ->field('payments', \Yii::t('deliverypayment', 'field_link_payment'), Editor::CHECK, ['listColumns' => ['flex' => 1]])
            ->buttonRowUpdate('TypeDeliveryForm')
            ->buttonRowDelete('TypeDeliveryDelete')
            ->buttonAddNew('TypeDeliveryForm');

        $this->_list
            ->setEditableFields($this->aEditFields, 'TypeDeliveryFastSave')
            ->buttonBack('list');
        $this->_list->enableDragAndDrop('TypeDeliverySort');
        $this->_list->setValue($this->aItems, $this->onPage, $this->page, $this->total);
    }
}
