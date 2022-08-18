<?php

namespace skewer\build\Adm\Articles\view;

use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\ext\view\ListView;

class Index extends ListView
{
    /** @var ArticlesRow[] */
    public $items = [];

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list

            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('articles', 'field_title'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('author', \Yii::t('articles', 'field_author'), 'string')
            ->field('publication_date', \Yii::t('articles', 'field_date'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('active', \Yii::t('articles', 'field_active'), 'check')
            ->field('on_main', \Yii::t('articles', 'field_on_main'), 'check')

            ->setValue($this->items, $this->onPage, $this->page, $this->total)

            ->setEditableFields(['active', 'on_main'], 'fastSave')

            ->buttonRowUpdate()
            ->buttonRowDelete()

            ->buttonAddNew('show');
    }
}
