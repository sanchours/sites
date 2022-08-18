<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 01.12.2016
 * Time: 14:01.
 */

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\ListView;

class StatusList extends ListView
{
    public $bIsSystemMode;
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if ($this->bIsSystemMode) {
            $this->_list->fieldShow('name', \Yii::t('order', 'status_name'), 's');
        }

        $this->_list->fieldString('title', \Yii::t('order', 'field_status'), ['listColumns' => ['flex' => 3]])
            ->setValue($this->aItems)
            ->setEditableFields(['title'], 'statusSave')
            ->buttonRowUpdate('statusShow')
            ->buttonRowDelete('statusDelete')
            ->buttonAddNew('statusShow')
            ->buttonBack();
    }
}
