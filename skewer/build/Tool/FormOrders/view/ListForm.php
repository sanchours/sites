<?php

declare(strict_types=1);

namespace skewer\build\Tool\FormOrders\view;

use skewer\components\ext\view\ListView;
use skewer\components\forms\forms\FieldAggregate;

class ListForm extends ListView
{
    /** @var FieldAggregate[] $fields */
    public $fields;
    public $formOrders;
    public $filter;
    public $notUseOneForm;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list->field('id', 'id', 'string')
            ->filterText('filter_id', $this->filter['id'], 'id');

        foreach ($this->fields as $field) {
            if ($field->settings->slug == 'person') {
                $this->_list->filterText(
                    'filter_' . $field->settings->slug,
                    $this->filter[$field->settings->slug],
                    $field->settings->title
                );
            }

            $this->_list->fieldString(
                $field->settings->slug,
                $field->settings->title,
                ['listColumns' => ['flex' => 1]]
            );
        }

        $this->_list
            ->fieldString('__add_date', \Yii::t('forms', 'add_date'))
            ->fieldString('__status', \Yii::t('forms', 'status'))
            ->widget(
                '__status',
                'skewer\\build\\Tool\\FormOrders\\Api',
                'getWidget4Status'
            )
            ->showCheckboxSelection()
            ->setValue(
                $this->formOrders,
                $this->onPage,
                $this->page,
                $this->total
            )
            ->buttonRowUpdate('Edit')
            ->buttonRowDelete('Delete')
            ->buttonAddNew('edit');
        if ($this->notUseOneForm) {
            $this->_list->buttonBack('ShowForms');
        }

        $this->_list
            ->buttonSeparator()
            ->buttonDeleteMultiple('deleteMultiple')
            ->buttonSeparator('->')
            ->buttonConfirm(
                'delAllOrders',
                \Yii::t('adm', 'del_all'),
                \Yii::t('forms', 'delAllOrders'),
                'icon-delete'
            );
    }
}
