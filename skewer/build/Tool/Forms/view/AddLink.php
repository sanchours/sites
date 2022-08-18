<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\FormView;

class AddLink extends FormView
{
    public $formFields;
    public $cardFields;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('form_field', \Yii::t('forms', 'form_field'), $this->formFields, [], false)
            ->fieldSelect('card_field', \Yii::t('forms', 'card_field'), $this->cardFields, [], false)
            ->setValue([])
            ->buttonSave('saveLink')
            ->buttonCancel('LinkList');
    }
}
