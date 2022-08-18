<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\FormView;

class CrmIntegrationEdit extends FormView
{
    public $form;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('idForm', 'id', 'hide')
            ->fieldCheck('settings_crm', \Yii::t('forms', 'form_send_crm'))
            ->setValue($this->form)
            ->buttonSave()
            ->buttonBack('Fields')
            ->button('CrmLinkList', \Yii::t('forms', 'crm_field_list'), 'icon-page');
    }
}
