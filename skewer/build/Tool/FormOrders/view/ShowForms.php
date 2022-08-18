<?php

declare(strict_types=1);

namespace skewer\build\Tool\FormOrders\view;

use skewer\components\ext\view\ListView;

class ShowForms extends ListView
{
    public $forms;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field(
                'settings_title',
                \Yii::t('forms', 'form_title'),
                'string',
                ['listColumns' => ['flex' => 1]]
            )
            ->setValue($this->forms)
            ->buttonRowUpdate('List');
    }
}
