<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.01.2017
 * Time: 11:32.
 */

namespace skewer\build\Tool\Crm\view;

use skewer\build\Tool\Crm\Api;
use skewer\components\ext\view\FormView;

class Index extends FormView
{
    public $sToken;
    public $sTokenEmail;
    public $sMail;
    public $sDomain;
    public $sIntegration;
    public $aValues;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('integration', $this->sIntegration, Api::getCRMIntegrationsList(), ['onUpdateAction' => 'saveIntegration']);

        switch ($this->aValues['integration']) {
            case Api::CRM_EMAIL_INTEGRATION:
                $this->_form
                    ->field('email', $this->sMail, 'str')
                    ->field('token_email', $this->sTokenEmail, 'str');
                break;
            case Api::CRM_API_INTEGRATION:
                $this->_form
                    ->field('domain', $this->sDomain, 'str')
                    ->field('token', $this->sToken, 'str');
                break;
        }

        $this->_form
            ->setValue($this->aValues)
            ->buttonSave('save');

        if ($this->aValues['token'] && $this->aValues['domain']) {
            $this->_form
                ->button('dealTypeList', \Yii::t('crm', 'deal_types_list_title'), 'icon-page')
                ->button('dealEventList', \Yii::t('crm', 'deal_events_list_title'), 'icon-page');
        }
    }
}
