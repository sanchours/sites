<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\FormView;
use skewer\components\forms\forms\SettingsFieldForm;

class EditField extends FormView
{
    public $fieldTypes;
    public $maxSizeOfFileSending;
    public $typesOfValidation;
    public $settings;
    public $typesShow;
    public $noSize;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_module->setPanelName(\Yii::t('forms', 'edit_field'));

        $this->_form
            ->fieldHide('idField', \Yii::t('forms', 'param_title'))
            ->fieldHide('idForm', \Yii::t('forms', 'param_title'))
            ->field('settings_title', \Yii::t('forms', 'param_title'), 'string')
            ->field('settings_slug', \Yii::t('forms', 'param_name'), 'string')
            ->field('settings_description', \Yii::t('forms', 'param_description'), 'text', ['labelAlign' => 'left'])
            ->fieldSelect(
                'type_name',
                \Yii::t('forms', 'param_type'),
                $this->fieldTypes,
                ['onUpdateAction' => 'changeType'],
                false
            )
            ->fieldSelect('type_typeOfValid', \Yii::t('forms', 'param_validation_type'), $this->typesOfValidation, [], false)
            ->fieldSelect(
                'type_displayType',
                \Yii::t('forms', 'field_f_link_id'),
                $this->typesShow,
                [],
                false
            )
            ->field('settings_required', \Yii::t('forms', 'param_required'), 'check')
            ->field('type_default', \Yii::t('forms', 'param_default'), 'text', [
                'labelAlign' => 'left',
                'subtext' => \Yii::t('forms', 'defaultDesc'),
            ]);

        $paramsMaxLengthField = [
            'labelAlign' => 'left',
            'subtext' => sprintf(\Yii::t('forms', 'maxlength_desc', [$this->maxSizeOfFileSending])),
            'default' => 0,
            'minValue' => 1
        ];

        $this->_form
            ->fieldInt(
                'type_maxLength',
                \Yii::t('forms', 'param_maxlength'),
                !$this->noSize ? $paramsMaxLengthField : $paramsMaxLengthField + ['disabled' => true]
            )
            ->fieldSelect(
                'settings_labelPosition',
                \Yii::t('forms', 'label_position'),
                SettingsFieldForm::getLocationsOfLabel(),
                ['margin' => '15 5 0 0'],
                false
            )
            ->field('settings_newLine', \Yii::t('forms', 'new_line'), 'check')
            ->field('settings_groupPrevField', \Yii::t('forms', 'group'), 'check')
            ->fieldSelect(
                'settings_widthFactor',
                \Yii::t('forms', 'width_factor'),
                SettingsFieldForm::getFactorsOfWidth(),
                [],
                false
            )
            ->field('settings_specStyle', \Yii::t('forms', 'param_man_params'), 'string', [
                'subtext' => \Yii::t('forms', 'manParamsDesc'),
            ])
            ->field('settings_classModify', \Yii::t('forms', 'field_class'), 'string', [
                'subtext' => \Yii::t('forms', 'mes4FieldClass'),
            ]);

        $this->_form->setValue($this->settings)
            ->buttonSave('SaveField')
            ->buttonBack('Fields');
    }
}
