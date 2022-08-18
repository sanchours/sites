<?php

namespace skewer\build\Tool\DeliveryPayment\view;

use skewer\base\ft\Editor;
use skewer\components\ext\view\ListView;

class TypePaymentIndex extends ListView
{
    public $aItems = [];

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', \Yii::t('deliverypayment', 'field_id'), Editor::INTEGER, ['listColumns.width' => 40])
            ->field('title', \Yii::t('deliverypayment', 'field_title'), Editor::STRING, ['listColumns' => ['flex' => 1]])
            ->field('alias', \Yii::t('deliverypayment', 'field_alias'), Editor::STRING, ['listColumns' => ['flex' => 1]])
            ->field('active', \Yii::t('deliverypayment', 'field_active'), Editor::CHECK, ['listColumns' => ['flex' => 1]])
            ->field('payment', \Yii::t('deliverypayment', 'field_payment'), Editor::STRING, ['listColumns' => ['flex' => 1]])
            ->field('message', \Yii::t('deliverypayment', 'field_message'), Editor::STRING, ['listColumns' => ['flex' => 2]])
            ->buttonRowUpdate('TypePaymentForm')
            ->buttonRowDelete('TypePaymentDelete')
            ->buttonAddNew('TypePaymentForm');

        $this->_list
            ->setEditableFields(['active'], 'TypePaymentFastSave')
            ->buttonBack('list');
        $this->_list->enableDragAndDrop('TypePaymentSort');

        $this->_list->setValue($this->aItems, $this->onPage, $this->page, $this->total);
    }
}
