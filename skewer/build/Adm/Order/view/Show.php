<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 05.12.2016
 * Time: 18:15.
 */

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $aStatusList;
    public $aPaymentList;
    public $aDeliveryList;
    public $aOrder;
    public $aFormRowKey;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form->field('id', 'ID', 'string', ['readOnly' => 1, 'disabled' => 1])
            ->fieldIf(in_array('date', $this->aFormRowKey), 'date', \Yii::t('order', 'field_date'), 'datetime')
            ->fieldIf(in_array('person', $this->aFormRowKey), 'person', \Yii::t('order', 'field_contact_face'), 'string')
            ->fieldIf(in_array('postcode', $this->aFormRowKey), 'postcode', \Yii::t('order', 'field_postcode'), 'string')
            ->fieldIf(in_array('address', $this->aFormRowKey), 'address', \Yii::t('order', 'field_address'), 'string')
            ->fieldIf(in_array('phone', $this->aFormRowKey), 'phone', \Yii::t('order', 'field_phone'), 'string')
            ->fieldIf(in_array('mail', $this->aFormRowKey), 'mail', \Yii::t('order', 'field_mail'), 'string')
            ->fieldSelect('status', \Yii::t('order', 'field_status'), $this->aStatusList, [], false);

        if (in_array('tp_deliv', $this->aFormRowKey)) {
            $this->_form
                ->fieldSelect('type_delivery', \Yii::t('order', 'field_delivery'), $this->aDeliveryList, ['onUpdateAction' => 'loadTypePayment'], false);
        }

        if (in_array('tp_pay', $this->aFormRowKey)) {
            $this->_form
                ->fieldSelect('type_payment', \Yii::t('order', 'field_payment'), $this->aPaymentList, [], false);
        }

        $this->_form
            ->fieldIf(in_array('text', $this->aFormRowKey), 'text', \Yii::t('order', 'field_text'), 'text')
            ->field('notes', \Yii::t('order', 'field_notes'), 'text')
            ->field('good', \Yii::t('order', 'goods_info'), 'show', ['labelAlign' => 'top'])

            ->field('history', \Yii::t('order', 'history_list'), 'show', ['labelAlign' => 'top'])

            ->setValue($this->aOrder)
            ->buttonSave()
            ->buttonBack();

        if (isset($this->aOrder['id']) && $this->aOrder['id']) {
            $this->_form
                ->buttonEdit('goodsShow', \Yii::t('order', 'field_goods_title_edit'))
                ->buttonSeparator()
                ->button('mailUpdGoodsList', \Yii::t('order', 'send_mail_upd_goods_list'), 'icon-page', '', ['tooltip' => \Yii::t('order', 'send_mail_upd_goods_list_tooltip')]);
        }

        $this->_form
            ->buttonSeparator('->')
            ->buttonDelete();
    }
}
