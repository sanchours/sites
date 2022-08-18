<?php

namespace skewer\build\Tool\DeliveryPayment\view;

use skewer\base\ft\Editor;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;
use skewer\components\ext\view\FormView;

class TypePaymentForm extends FormView
{
    /** @var TypePayment */
    public $item;
    public $aPaymentSystems;

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
            ->field('message', \Yii::t('deliverypayment', 'field_message'), Editor::STRING);

        if ($this->aPaymentSystems) {
            $this->_form->fieldSelect('payment', \Yii::t('deliverypayment', 'field_payment'), $this->aPaymentSystems, [], false);
        }

        $this->_form
            ->buttonSave('TypePaymentSave')
            ->buttonBack('TypePaymentList');

        if ($this->item->id) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete('TypePaymentDelete');
        }

        $this->_form->setValue($this->item);
    }
}
