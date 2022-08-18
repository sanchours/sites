<?php

namespace skewer\build\Adm\News\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    /** @var array News[] */
    public $items = [];

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])

            ->field('title', \Yii::t('news', 'field_title'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('publication_date', \Yii::t('news', 'field_date'), 'datetime', ['listColumns' => ['flex' => 2]])
            ->field('active', \Yii::t('news', 'field_active'), 'check')
            ->field('on_main', \Yii::t('news', 'field_onmain'), 'check')

            ->buttonRowUpdate()
            ->buttonRowDelete()

            ->buttonAddNew('new');

        $this->_list->setValue($this->items, $this->onPage, $this->page, $this->total);

        $this->_list->setEditableFields(['active', 'on_main'], 'saveFromList');
    }
}
