<?php

declare(strict_types=1);

namespace skewer\build\Tool\FormOrders\view;

use skewer\components\ext\view\FormView;
use skewer\components\forms\components\dto\FieldFormBuilderByType;

class Edit extends FormView
{
    /** @var FieldFormBuilderByType[] */
    public $fields;
    public $statusList;
    public $formOrder;
    public $bCanDelete;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->field('id', 'id', 'hide');

        foreach ($this->fields as $field) {
            $field->fieldObject->addFieldInFormInterface(
                $this->_form,
                $field
            );
        }
        $this->_form
            ->field(
                '__add_date',
                \Yii::t('forms', 'add_date'),
                'string',
                ['disabled' => true]
            )
            ->field(
                '__section',
                \Yii::t('forms', 'section'),
                'string',
                ['disabled' => true]
            )
            ->fieldSelect(
                '__status',
                \Yii::t('forms', 'status'),
                $this->statusList
            )
            ->setValue($this->formOrder)
            ->buttonSave('save')
            ->buttonCancel('list');

        if ($this->bCanDelete) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete();
        }
    }
}
