<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\FormView;

class CrmLinkEdit extends FormView
{
    public $fields;
    public $link;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id')
            ->fieldShow('title', \Yii::t('forms', 'crm_field_title'))
            ->fieldSelect('fieldId', \Yii::t('forms', 'form_field'), $this->fields)
            ->fieldCheck('required', \Yii::t('forms', 'param_required'))
            ->setValue($this->link)
            ->buttonSave('saveCrmLink')
            ->buttonCancel('CrmLinkList');
    }
}
