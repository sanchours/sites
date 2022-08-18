<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\build\Tool\Crm\Api as CrmApi;
use skewer\components\ext\view\FormView;
use skewer\components\targets\models\Targets;

class CreateForm extends FormView
{
    public $form;
    public $systemMode;
    public $typesHandler;
    public $handlerSubtext;
    public $hasFormTarget;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $handlerValueLabel = $this->systemMode
            ? \Yii::t('forms', 'form_handler_value')
            : \Yii::t('forms', 'formHandlerEmailTitle');

        $this->_form
            ->field('idForm', 'id', 'hide')
            ->fieldString('settings_title', \Yii::t('forms', 'form_title'))
            ->fieldIf($this->systemMode, 'settings_slug', \Yii::t('forms', 'form_name'), 'string')
            ->fieldSelect('handler_type', \Yii::t('forms', 'form_handler_type'), $this->typesHandler, [], false)
            ->fieldString('handler_value', $handlerValueLabel, [
                'subtext' => $this->handlerSubtext,
            ])
            ->fieldCheck('protection_captcha', \Yii::t('forms', 'form_captcha'), [
                'margin' => '15 0 0 0',
                'cls' => 'sk-form-captcha',
            ])
            ->fieldCheck('protection_hideField', \Yii::t('forms', 'form_hide_field'))
            ->fieldCheck('protection_blockJs', \Yii::t('forms', 'form_block_js'), [
                'margin' => '0 0 15 0',
            ]);

        if ($this->systemMode) {
            $this->_form->fieldCheck('settings_system', \Yii::t('forms', 'form_sys'));
        }

        if ($this->hasFormTarget) {
            $this->_form
                ->fieldSelect(
                    'target_yandex',
                    \Yii::t('forms', 'form_target'),
                    Targets::getByTypeArray('yandex')
                )
                ->fieldSelect(
                    'target_google',
                    \Yii::t('forms', 'form_target_google'),
                    Targets::getByTypeArray('google')
                );
        }

        $this->_form->fieldIf(CrmApi::isCrmInstall(), 'settings_crm', \Yii::t('forms', 'form_send_crm'), 'check');

        if ($this->systemMode) {
            $this->_form->fieldString('settings_template', \Yii::t('forms', 'template'));
        }

        $this->_form->fieldCheck(
            'settings_showRequiredFields',
            \Yii::t('forms', 'show_phrase_required_fields')
        )
            ->fieldCheck('settings_showHeader', \Yii::t('forms', 'form_show_header'))
            ->fieldCheck('settings_emailInReply', \Yii::t('forms', 'replyTo'))
            ->fieldCheck('settings_noSendDataInLetter', \Yii::t('forms', 'notification_letter'), [
                'subtext' => \Yii::t('forms', 'form_notific_value_default'),
            ]);

        $this->_form
            ->fieldString('settings_button', \Yii::t('forms', 'form_button'))
            ->fieldString('settings_class', \Yii::t('forms', 'form_class'))
            ->fieldCheck('settings_showCheckBack', \Yii::t('forms', 'check_back'))
            ->setValue($this->form)
            ->buttonSave()
            ->buttonBack('Init');
    }
}
