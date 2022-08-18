<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\ListView;

class LinkList extends ListView
{
    public $links;

    public function build()
    {
        $this->_list
            ->field('form_field', \Yii::t('forms', 'form_field'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('card_field', \Yii::t('forms', 'card_field'), 'string', ['listColumns' => ['flex' => 1]])
            ->setValue($this->links)
            ->buttonAddNew('addLink')
            ->buttonCancel('Fields')
            ->buttonRowDelete('delLink');
    }
}
