<?php

namespace skewer\build\Tool\Labels\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $labels;
    public $search;

    public function build()
    {
        $this->_module->setPanelName(\Yii::t('labels', 'title_list'));

        $this->_list
            ->filterText('search', $this->search)
            ->field('id', \Yii::t('labels', 'id'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('title', \Yii::t('labels', 'title'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('alias', \Yii::t('labels', 'alias'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('default', \Yii::t('labels', 'default'), 'string', ['listColumns' => ['flex' => 5]])
            ->buttonRowUpdate()
            ->buttonRowDelete()
            ->buttonAddNew('Show', \Yii::t('labels', 'btn_add'))
            ->setValue($this->labels);
    }
}
