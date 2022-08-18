<?php

namespace skewer\build\Tool\DeliveryPayment\view;

use skewer\base\ft\Editor;
use skewer\components\ext\view\FormView;

class TypeDeliveryForm extends FormView
{
    public $item;
    public $aPayments;
    public $paidDelivery;
    public $data;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', \Yii::t('deliverypayment', 'field_id'), 'hide')
            ->field('title', \Yii::t('deliverypayment', 'field_title'), Editor::STRING)
            ->field('alias', \Yii::t('deliverypayment', 'field_alias'), Editor::STRING, ['disabled' => (bool) $this->item->id])
            ->field('active', \Yii::t('deliverypayment', 'field_active'), Editor::CHECK)
            ->field('free_shipping', \Yii::t('deliverypayment', 'field_free_shipping'), Editor::CHECK, ['disabled' => !(bool) $this->paidDelivery])
            ->field('min_cost', \Yii::t('deliverypayment', 'field_min_cost'), Editor::MONEY, ['disabled' => (!(bool) $this->paidDelivery || !(bool) $this->item->free_shipping)])
            ->field('price', \Yii::t('deliverypayment', 'field_price'), Editor::MONEY, ['disabled' => !(bool) $this->paidDelivery])
            ->field('address', \Yii::t('deliverypayment', 'field_address'), Editor::STRING, ['disabled' => !(bool) $this->paidDelivery])
            ->field('coord_deliv_costs', \Yii::t('deliverypayment', 'field_coord_deliv_costs'), Editor::CHECK, ['disabled' => !(bool) $this->paidDelivery]);

        if ($this->aPayments) {
            $this->_form->fieldMultiSelect('payments', \Yii::t('deliverypayment', 'field_typepayment'), $this->aPayments);
        }

        $this->_form
            ->buttonSave('TypeDeliverySave')
            ->buttonBack('TypeDeliveryList');

        if ($this->item->id) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete('TypeDeliveryDelete');
        }

        $this->_form->setValue($this->data);
    }
}
