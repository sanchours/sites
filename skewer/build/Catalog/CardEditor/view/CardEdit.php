<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 29.12.2016
 * Time: 16:28.
 */

namespace skewer\build\Catalog\CardEditor\view;

use skewer\components\ext\view\FormView;

class CardEdit extends FormView
{
    public $iCardId;
    public $bIsBasic;
    public $aBasicCardList;
    public $oCard;
    public $sPaymentObject;
    public $aPaymentObject;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('card', 'field_title'), ['listColumns.flex' => 1])
            ->fieldString('name', \Yii::t('card', 'field_name'), ['disabled' => (bool) $this->iCardId])
            ->fieldSelect('payment_object', \Yii::t('card', 'field_payment_object'), $this->aPaymentObject);
        if ($this->bIsBasic) {
            $this->_form
                ->fieldHide('parent', \Yii::t('card', 'field_base_card'), 's')
                ->fieldHide('type', '');
        } else {
            $this->_form
                ->fieldSelect('parent', \Yii::t('card', 'field_base_card'), $this->aBasicCardList, [], false)
                ->fieldHide('type', '', 's');

            $this->_form
                ->fieldCheck('hide_detail', \Yii::t('card', 'field_hide_detail'));
        }
        $this->_form
            ->setValue($this->oCard)
            ->setValue(['payment_object' => $this->sPaymentObject])
            ->buttonSave('CardSave')
            ->buttonCancel($this->iCardId ? 'FieldList' : 'CardList');
    }
}
