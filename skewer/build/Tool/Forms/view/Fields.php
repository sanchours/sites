<?php

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\ListView;

class Fields extends ListView
{
    public $fields;
    public $hasCatalog;
    public $hasCRM;
    public $formName;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_module->setPanelName(\Yii::t('forms', 'field_list') . " {$this->formName}");

        $this->_list
            ->field('settings_title', \Yii::t('forms', 'param_title'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('settings_slug', \Yii::t('forms', 'param_name'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('type_title', \Yii::t('forms', 'param_type'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('settings_required', \Yii::t('forms', 'param_required'), 'check', ['listColumns' => ['width' => 140]])

            ->enableDragAndDrop('sortFieldList')

            ->setValue($this->fields)

            ->buttonRowUpdate('editField')
            ->buttonRowDelete('deleteField')
            ->buttonAddNew('EditField')
            ->buttonBack('forms')
            ->buttonSeparator()
            ->buttonEdit('EditForm', \Yii::t('forms', 'from_settings'))
            ->buttonIf($this->hasCatalog, \Yii::t('forms', 'elConnectText'), 'linkList', 'icon-link')
            ->buttonIf($this->hasCRM, \Yii::t('forms', 'elCrmIntegrationText'), 'CRMIntegration', 'icon-link')
            ->buttonSeparator()
            ->buttonEdit('editResultPage', \Yii::t('forms', 'settingsResultPage'))
            ->buttonEdit('answer', \Yii::t('forms', 'answerDetailText'))
            ->buttonEdit('agreed', \Yii::t('forms', 'privacy_policy_button'));
    }
}
