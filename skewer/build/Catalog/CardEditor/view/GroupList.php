<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 30.12.2016
 * Time: 10:24.
 */

namespace skewer\build\Catalog\CardEditor\view;

use skewer\components\ext\view\ListView;

class GroupList extends ListView
{
    public $aCardGroup;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->fieldShow('id', 'id', 'i')
            ->fieldString('name', \Yii::t('card', 'field_g_name'))
            ->fieldString('title', \Yii::t('card', 'field_g_title'), ['listColumns.flex' => 1])
            ->setValue($this->aCardGroup)
            ->buttonAddNew('GroupEdit')
            ->buttonCancel('CardList')
            ->buttonRowUpdate('GroupEdit')
            ->buttonRowDelete('GroupRemove')
            ->enableDragAndDrop('sortGroups');
    }
}
