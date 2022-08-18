<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 11:38.
 */

namespace skewer\build\Adm\Tooltip\view;

use skewer\components\ext;

class GetList extends ext\view\ListView
{
    public $items = [];

    protected function getLibFileName()
    {
        return 'TooltipList';
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('status', 'ID', 'hide')
            ->field('name', \Yii::t('tooltip', 'field_name'), 'string')
            ->fieldCheck('status_tmp', \Yii::t('tooltip', 'field_status'))

            ->buttonRowUpdate('form')
            ->buttonRowDelete()

            ->setEditableFields(['status_tmp'], 'Check')

            ->buttonCustomExt(
                ext\docked\AddBtn::create()
                    ->setAction('Form')
            );

        $this->_list->setValue($this->items);
    }
}
