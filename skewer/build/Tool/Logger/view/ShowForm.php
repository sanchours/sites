<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 11:53.
 */

namespace skewer\build\Tool\Logger\view;

use skewer\components\ext\view\FormView;

class ShowForm extends FormView
{
    public $bIsSystemMode;
    public $bItemExists;
    public $aItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldShow('id', \Yii::t('Logger', 'field_id'))
            ->fieldShow('event_time', \Yii::t('Logger', 'field_event_time'))
            ->fieldShow('event_type', \Yii::t('Logger', 'field_event_type'))
            ->fieldShow('log_type', \Yii::t('Logger', 'field_log_type'))
            ->fieldShow('title', \Yii::t('Logger', 'field_title'))
            ->fieldShow('module', \Yii::t('Logger', 'field_module'))
            ->fieldShow('initiator', \Yii::t('Logger', 'field_initiator'))
            ->fieldShow('user', \Yii::t('Logger', 'user'))
            ->fieldShow('ip', \Yii::t('Logger', 'field_ip'))
            ->fieldShow('proxy_ip', \Yii::t('Logger', 'field_proxy_ip'))
            ->fieldShow('external_id', \Yii::t('Logger', 'field_external_id'));

        if ($this->bIsSystemMode) {
            $this->_form->fieldShow('description', \Yii::t('Logger', 'field_description'), 's', ['labelAlign' => 'top']);
        }
        $this->_form->buttonCancel();

        if ($this->bItemExists) {
            $this->_form->setValue($this->aItem);
        }
    }
}
