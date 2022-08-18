<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.12.2016
 * Time: 14:29.
 */

namespace skewer\build\Catalog\Collections\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $oCollections;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->fieldShow('id', 'id', 'i', ['listColumns.width' => 40])
            ->fieldString('title', \Yii::t('collections', 'coll_name'), ['listColumns.flex' => 1])
            ->setValue($this->oCollections, $this->onPage, $this->page, $this->total)
            ->buttonAddNew('Edit', \Yii::t('collections', 'create_coll'))
            ->buttonRowUpdate('View')
            ->buttonRowDelete('Remove');
    }
}
