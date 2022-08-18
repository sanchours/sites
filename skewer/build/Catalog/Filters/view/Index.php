<?php

namespace skewer\build\Catalog\Filters\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $items = [];

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])
            ->field('title', \Yii::t('news', 'field_title'), 'string', ['listColumns' => ['flex' => 3]])

            ->buttonRowUpdate()
            ->buttonRowDelete('delete', 'delete', '')

            ->buttonAddNew('new');

        $this->_list->setValue($this->items);
    }
}
