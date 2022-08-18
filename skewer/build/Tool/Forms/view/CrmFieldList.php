<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\ListView;

class CrmFieldList extends ListView
{
    public $fields;

    public function build()
    {
        $this->_list
            ->fieldHide('id')
            ->fieldString('title', \Yii::t('forms', 'crm_field_title'), ['listColumns' => ['flex' => 1]])
            ->fieldString('fieldTitle', \Yii::t('forms', 'form_field'), ['listColumns' => ['flex' => 1]])
            ->fieldCheck('required', \Yii::t('forms', 'param_required'), ['listColumns' => ['flex' => 1]])
            ->fieldhide('mark', 'form_field_required', 'i', ['listColumns' => ['width' => 0]])
            ->setHighlighting('mark', \Yii::t('forms', 'form_field_required_one'), '1')
            ->setValue($this->fields)
            ->buttonRowUpdate('editCrmLink')
            ->buttonBack('CRMIntegration');
    }
}
