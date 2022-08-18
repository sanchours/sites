<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 05.12.2016
 * Time: 18:15.
 */

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\FormView;

class LoadTypePayment extends FormView
{
    public $aPaymentList;
    public $aItem;
    public $aFormRowKey;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if (in_array('tp_pay', $this->aFormRowKey)) {
            $this->_form
                ->fieldSelect('type_payment', \Yii::t('order', 'field_payment'), $this->aPaymentList, [], false)
                ->setValue($this->aItem);
        }
    }
}
