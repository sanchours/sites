<?php

namespace skewer\build\Tool\DeliveryPayment\view;

use skewer\base\ft\Editor;
use skewer\components\ext\view\FormView;

class Index extends FormView
{
    public $data;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->field('paid_delivery', \Yii::t('deliverypayment', 'paid_delivery'), Editor::CHECK, ['onUpdateAction' => 'paidDelivery'])
            ->setValue($this->data)
            ->buttonEdit('TypePaymentList', \Yii::t('deliverypayment', 'button_typepayment'), ['unsetFormDirtyBlocker' => true])
            ->buttonEdit('TypeDeliveryList', \Yii::t('deliverypayment', 'button_typedelivery'), ['unsetFormDirtyBlocker' => true]);
    }
}
